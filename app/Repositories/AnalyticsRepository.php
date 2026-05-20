<?php

namespace App\Repositories;

use App\Enums\IrrigationSourceType;
use App\Enums\LandHoldingCategory;
use App\Models\Crop;
use App\Models\CropPattern;
use App\Models\Farmer;
use App\Models\IrrigationRecord;
use App\Models\LandHolding;
use App\Models\Region;
use App\Models\Well;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Services\ExternalAgriApiClient;
use App\Support\ChartPalette;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function __construct(
        private readonly ExternalAgriApiClient $externalApiClient,
    ) {}

    public function getDashboardKpis(?int $regionId = null, ?string $state = null, ?string $season = null, ?int $year = null): array
    {
        $cacheKey = 'analytics.kpis.'.($regionId ?? 'all').'.'.($state ?? 'all').'.'.($season ?? 'all').'.'.($year ?? 'all');

        return Cache::remember($cacheKey, 300, function () use ($regionId, $state, $season, $year) {
            $farmerQuery = Farmer::query();
            $holdingQuery = LandHolding::query();
            $wellQuery = Well::query();
            $cropQuery = CropPattern::query();

            $this->applyRegionScope($farmerQuery, $regionId, $state);
            $this->applyRegionScope($holdingQuery, $regionId, $state);
            $this->applyRegionScope($wellQuery, $regionId, $state);
            $this->applyRegionScope($cropQuery, $regionId, $state);

            if ($year) {
                $cropQuery->where('year', $year);
            }

            if ($season) {
                $cropQuery->where('season', $season);

                // Filter farmers who had crop patterns in this season
                $farmerQuery->whereHas('landHoldings.cropPatterns', function ($q) use ($season, $year) {
                    $q->where('season', $season);
                    if ($year) {
                        $q->where('year', $year);
                    }
                });

                // Total cultivated area is the sum of crop patterns matching this season
                $totalLand = (float) $cropQuery->sum('area_hectares');

                // Total irrigated area is the sum of crop patterns matching this season that belong to irrigated land holdings
                $irrigatedLand = (float) (clone $cropQuery)
                    ->whereHas('landHolding', fn ($q) => $q->where('is_irrigated', true))
                    ->sum('area_hectares');
            } else {
                $totalLand = (float) $holdingQuery->sum('area_hectares');
                $irrigatedLand = (float) (clone $holdingQuery)->where('is_irrigated', true)->sum('area_hectares');
            }

            $totalFarmers = $farmerQuery->count();
            $avgWellDepth = (float) $wellQuery->avg('depth_feet');

            $cropDiversity = (clone $cropQuery)->distinct('crop_id')->count('crop_id');

            return [
                'total_farmers' => $totalFarmers,
                'total_cultivated_land' => round($totalLand, 2),
                'irrigated_land' => round($irrigatedLand, 2),
                'non_irrigated_land' => round($totalLand - $irrigatedLand, 2),
                'avg_well_depth' => round($avgWellDepth, 1),
                'crop_diversity_index' => $cropDiversity,
                'irrigation_ratio' => $totalLand > 0 ? round(($irrigatedLand / $totalLand) * 100, 1) : 0,
            ];
        });
    }

    public function getHoldingDistribution(?int $regionId = null, ?string $state = null): array
    {
        return Cache::remember($this->cacheKey('analytics.holdings', compact('regionId', 'state')), 600, function () use ($regionId, $state) {
            $query = LandHolding::query();
            $this->applyRegionScope($query, $regionId, $state);

            $rows = $query
                ->select('category', DB::raw('COUNT(*) as count'), DB::raw('SUM(area_hectares) as total_area'))
                ->groupBy('category')
                ->get();

            $order = array_flip(array_map(fn (LandHoldingCategory $c) => $c->value, LandHoldingCategory::cases()));

            return $rows
                ->map(function ($row) {
                    $category = $row->category instanceof LandHoldingCategory
                        ? $row->category
                        : LandHoldingCategory::from($row->category);

                    return [
                        'category' => $category->value,
                        'label' => $category->label(),
                        'count' => (int) $row->count,
                        'total_area' => round((float) $row->total_area, 2),
                        'color' => ChartPalette::holdingColor($category->value),
                    ];
                })
                ->sortBy(fn (array $row) => $order[$row['category']] ?? 99)
                ->values()
                ->all();
        });
    }

    public function getIrrigationBreakdown(?int $regionId = null, ?string $state = null, ?string $irrigationSource = null): array
    {
        return Cache::remember($this->cacheKey('analytics.irrigation', compact('regionId', 'state', 'irrigationSource')), 600, function () use ($regionId, $state, $irrigationSource) {
            $query = IrrigationRecord::query();
            $this->applyRegionScope($query, $regionId, $state);

            if ($irrigationSource) {
                $query->where('source_type', $irrigationSource);
            }

            return $query
                ->select('source_type', DB::raw('COUNT(*) as count'))
                ->groupBy('source_type')
                ->orderByDesc('count')
                ->get()
                ->map(function ($row) {
                    $source = $row->source_type instanceof IrrigationSourceType
                        ? $row->source_type
                        : IrrigationSourceType::from($row->source_type);

                    return [
                        'source' => $source->value,
                        'label' => $source->label(),
                        'count' => (int) $row->count,
                        'color' => ChartPalette::irrigationColor($source->value),
                    ];
                })
                ->all();
        });
    }

    public function getCropDistribution(?int $regionId = null, ?string $state = null, ?string $season = null, ?int $year = null): array
    {
        $cacheKey = $this->cacheKey('analytics.crops', compact('regionId', 'state', 'season', 'year'));

        return Cache::remember($cacheKey, 600, function () use ($regionId, $state, $season, $year) {
            if (config('agrolens.fetch_on_poll', false) && $this->externalApiClient->isConfigured()) {
                $external = $this->externalApiClient->fetchCropRecords([
                    'state' => $state,
                    'season' => $season,
                    'year' => $year,
                ]);

                if (is_array($external) && isset($external['records'])) {
                    return $this->transformExternalCropRecords($external['records']);
                }
            }

            $query = CropPattern::query()->with('crop:id,name');
            $this->applyRegionScope($query, $regionId, $state);

            if ($season) {
                $query->where('season', $season);
            }

            if ($year) {
                $query->where('year', $year);
            }

            $rows = $query
                ->select('crop_id', DB::raw('SUM(area_hectares) as total_area'))
                ->groupBy('crop_id')
                ->orderByDesc('total_area')
                ->get();

            $totalArea = $rows->sum(fn ($row) => (float) $row->total_area);

            $distribution = $rows
                ->values()
                ->map(function ($row, int $index) use ($totalArea) {
                    $area = round((float) $row->total_area, 2);
                    $cropName = $row->crop?->name ?? 'Unknown';

                    return [
                        'crop' => $cropName,
                        'area' => $area,
                        'percentage' => $totalArea > 0 ? round(($area / $totalArea) * 100, 1) : 0,
                        'color' => ChartPalette::cropColor($cropName, $index),
                    ];
                });

            return $this->normalizePercentageTotal($distribution, $totalArea);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    private function transformExternalCropRecords(array $records): array
    {
        $mode = $this->detectExternalMode($records);

        return $mode === 'commodity_prices'
            ? $this->transformExternalCommodityPriceDistribution($records)
            : $this->transformExternalCropDistribution($records);
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    private function detectExternalMode(array $records): string
    {
        $first = $records[0] ?? [];

        if (isset($first['modal_price']) || isset($first['commodity']) || isset($first['commodity_name'])) {
            return 'commodity_prices';
        }

        return 'crop_area';
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    private function transformExternalCropDistribution(array $records): array
    {
        $grouped = collect($records)
            ->map(fn ($record) => [
                'crop' => $this->normalizeExternalCropName($record),
                'area' => $this->normalizeExternalArea($record),
            ])
            ->filter(fn ($item) => filled($item['crop']) && $item['area'] > 0)
            ->groupBy('crop')
            ->map(fn ($rows, $crop) => [
                'crop' => $crop,
                'area' => round($rows->sum('area'), 2),
            ])
            ->sortByDesc('area');

        $totalArea = $grouped->sum('area');

        $distribution = $grouped->values()->map(function ($item, int $index) use ($totalArea) {
            $area = $item['area'];

            return [
                'crop' => $item['crop'],
                'area' => $area,
                'percentage' => $totalArea > 0 ? round(($area / $totalArea) * 100, 1) : 0,
                'color' => ChartPalette::cropColor($item['crop'], $index),
            ];
        });

        return $this->normalizePercentageTotal($distribution, $totalArea);
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    private function transformExternalCommodityPriceDistribution(array $records): array
    {
        $grouped = collect($records)
            ->map(fn ($record) => [
                'crop' => $this->normalizeExternalCropName($record),
                'area' => $this->normalizeExternalPrice($record),
            ])
            ->filter(fn ($item) => filled($item['crop']) && $item['area'] > 0)
            ->groupBy('crop')
            ->map(fn ($rows, $crop) => [
                'crop' => $crop,
                'area' => round($rows->sum('area'), 2),
            ])
            ->sortByDesc('area');

        $totalAmount = $grouped->sum('area');

        $distribution = $grouped->values()->map(function ($item, int $index) use ($totalAmount) {
            $area = $item['area'];

            return [
                'crop' => $item['crop'],
                'area' => $area,
                'percentage' => $totalAmount > 0 ? round(($area / $totalAmount) * 100, 1) : 0,
                'color' => ChartPalette::cropColor($item['crop'], $index),
            ];
        });

        return $this->normalizePercentageTotal($distribution, $totalAmount);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizePercentageTotal(Collection $rows, float $total): array
    {
        if ($rows->isEmpty() || $total <= 0) {
            return $rows->map(fn (array $row) => [...$row, 'percentage' => 0])->all();
        }

        $running = 0.0;
        $lastIndex = $rows->count() - 1;

        return $rows->values()
            ->map(function (array $row, int $index) use (&$running, $lastIndex) {
                if ($index === $lastIndex) {
                    $row['percentage'] = max(0, round(100 - $running, 1));

                    return $row;
                }

                $percentage = round((float) $row['percentage'], 1);
                $running += $percentage;
                $row['percentage'] = $percentage;

                return $row;
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function normalizeExternalCropName(array $record): string
    {
        return trim((string) ($record['crop'] ?? $record['commodity'] ?? $record['crop_name'] ?? $record['commodity_name'] ?? $record['name'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function normalizeExternalArea(array $record): float
    {
        return (float) ($record['area'] ?? $record['area_hectares'] ?? $record['total_area'] ?? $record['hectares'] ?? $record['value'] ?? 0);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function normalizeExternalPrice(array $record): float
    {
        return (float) ($record['modal_price'] ?? $record['price'] ?? $record['value'] ?? 0);
    }

    public function getWellDepthByRegion(?int $regionId = null, ?string $state = null): array
    {
        return Cache::remember($this->cacheKey('analytics.wells', compact('regionId', 'state')), 600, function () use ($regionId, $state) {
            $query = Well::query()->with('region:id,name,state');
            $this->applyRegionScope($query, $regionId, $state);

            return $query
                ->select('region_id', DB::raw('AVG(depth_feet) as avg_depth'), DB::raw('COUNT(*) as well_count'))
                ->groupBy('region_id')
                ->orderBy('region_id')
                ->get()
                ->map(fn ($row) => [
                    'region' => $row->region?->name ?? 'Unknown',
                    'state' => $row->region?->state,
                    'avg_depth' => round((float) $row->avg_depth, 1),
                    'well_count' => (int) $row->well_count,
                ])
                ->all();
        });
    }

    public function getMapMarkers(?string $state = null, ?int $year = null, ?int $regionId = null): array
    {
        return Cache::remember($this->cacheKey('analytics.map', compact('state', 'year', 'regionId')), 600, function () use ($state, $year, $regionId) {
            $regions = Region::query()
                ->districts()
                ->when($regionId, fn ($q) => $q->whereKey($regionId))
                ->when($state, fn ($q) => $q->where('state', $state))
                ->whereNotNull('latitude')
                ->withCount(['farmers', 'wells'])
                ->withSum('landHoldings as cultivated_land', 'area_hectares')
                ->orderBy('state')
                ->orderBy('name')
                ->get();

            if ($regions->isEmpty()) {
                return [];
            }

            $regionIds = $regions->pluck('id');

            $cropQuery = CropPattern::query()
                ->whereIn('region_id', $regionIds)
                ->select('region_id', 'crop_id', DB::raw('SUM(area_hectares) as total_area'))
                ->groupBy('region_id', 'crop_id');

            if ($year) {
                $cropQuery->where('year', $year);
            }

            $cropNames = Crop::query()->pluck('name', 'id');
            $topCropsByRegion = $cropQuery
                ->get()
                ->groupBy('region_id')
                ->map(function ($group) use ($cropNames) {
                    $top = $group->sortByDesc('total_area')->first();

                    return $cropNames[$top?->crop_id] ?? null;
                });

            $irrigatedByRegion = LandHolding::query()
                ->whereIn('region_id', $regionIds)
                ->select('region_id', DB::raw('SUM(CASE WHEN is_irrigated = 1 THEN area_hectares ELSE 0 END) as irrigated'))
                ->groupBy('region_id')
                ->pluck('irrigated', 'region_id');

            $minFarmers = (int) $regions->min('farmers_count');
            $maxFarmers = max(1, (int) $regions->max('farmers_count'));

            return $regions
                ->map(function (Region $region) use ($topCropsByRegion, $irrigatedByRegion, $minFarmers, $maxFarmers) {
                    $cultivated = round((float) ($region->cultivated_land ?? 0), 1);
                    $irrigated = round((float) ($irrigatedByRegion[$region->id] ?? 0), 1);
                    $irrigationPct = $cultivated > 0 ? round(($irrigated / $cultivated) * 100, 1) : 0;
                    $markerColor = $minFarmers === $maxFarmers
                        ? '#f59e0b'
                        : ChartPalette::mapMarkerColor($region->farmers_count, $maxFarmers);

                    return [
                        'id' => $region->id,
                        'name' => $region->name,
                        'state' => $region->state,
                        'zone' => $region->agricultural_zone,
                        'lat' => (float) $region->latitude,
                        'lng' => (float) $region->longitude,
                        'farmers' => $region->farmers_count,
                        'wells' => $region->wells_count,
                        'cultivated_land' => $cultivated,
                        'irrigation_pct' => $irrigationPct,
                        'top_crop' => $topCropsByRegion[$region->id] ?? '—',
                        'color' => $markerColor,
                    ];
                })
                ->all();
        });
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    private function cacheKey(string $prefix, array $parts): string
    {
        return $prefix.'.'.md5(json_encode($parts));
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyRegionScope(Builder $query, ?int $regionId, ?string $state, string $column = 'region_id'): void
    {
        if ($regionId) {
            $query->where($column, $regionId);

            return;
        }

        if ($state) {
            $query->whereIn($column, Region::query()->where('state', $state)->pluck('id'));
        }
    }
}
