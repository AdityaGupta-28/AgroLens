<?php

namespace App\Services;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;

class RealtimeAnalyticsService
{
    public function __construct(
        private readonly AnalyticsRepositoryInterface $analytics
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function dashboardData(array $filters = [], bool $fetchLiveApi = true): array
    {
        $regionId = isset($filters['region_id']) ? (int) $filters['region_id'] : null;
        $state = $filters['state'] ?? null;
        $season = $filters['season'] ?? null;
        $year = isset($filters['year']) ? (int) $filters['year'] : null;
        $irrigationSource = $filters['irrigation_source'] ?? null;

        return [
            'kpis' => $this->analytics->getDashboardKpis($regionId, $state, $season, $year),
            'holding_distribution' => $this->analytics->getHoldingDistribution($regionId, $state),
            'irrigation_breakdown' => $this->analytics->getIrrigationBreakdown($regionId, $state, $irrigationSource),
            'crop_distribution' => $this->analytics->getCropDistribution($regionId, $state, $season, $year),
            'well_depths' => $this->analytics->getWellDepthByRegion($regionId, $state),
            'map_markers' => $this->analytics->getMapMarkers($state, $year, $regionId),
            'data_source' => 'database',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    public function transformCropDistribution(array $records): array
    {
        $grouped = collect($records)
            ->map(fn ($record) => [
                'crop' => trim((string) ($record['crop'] ?? $record['crop_name'] ?? $record['name'] ?? '')),
                'area' => (float) ($record['area'] ?? $record['area_hectares'] ?? $record['total_area'] ?? $record['value'] ?? 0),
            ])
            ->filter(fn ($item) => filled($item['crop']) && $item['area'] > 0)
            ->groupBy('crop')
            ->map(fn ($rows, $crop) => [
                'crop' => $crop,
                'area' => round($rows->sum('area'), 2),
            ])
            ->sortByDesc('area');

        $totalArea = $grouped->sum('area');

        return $grouped->values()->map(function ($item, int $index) use ($totalArea) {
            $area = $item['area'];

            return [
                'crop' => $item['crop'],
                'area' => $area,
                'percentage' => $totalArea > 0 ? round(($area / $totalArea) * 100, 1) : 0,
            ];
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    public function transformCommodityPriceDistribution(array $records): array
    {
        $grouped = collect($records)
            ->map(fn ($record) => [
                'crop' => trim((string) ($record['commodity'] ?? $record['commodity_name'] ?? $record['crop'] ?? $record['name'] ?? '')),
                'area' => (float) ($record['modal_price'] ?? $record['price'] ?? $record['value'] ?? 0),
            ])
            ->filter(fn ($item) => filled($item['crop']) && $item['area'] > 0)
            ->groupBy('crop')
            ->map(fn ($rows, $crop) => [
                'crop' => $crop,
                'area' => round($rows->sum('area'), 2),
            ])
            ->sortByDesc('area');

        $totalAmount = $grouped->sum('area');

        return $grouped->values()->map(function ($item, int $index) use ($totalAmount) {
            $area = $item['area'];

            return [
                'crop' => $item['crop'],
                'area' => $area,
                'percentage' => $totalAmount > 0 ? round(($area / $totalAmount) * 100, 1) : 0,
            ];
        })->all();
    }
}
