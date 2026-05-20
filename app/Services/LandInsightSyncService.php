<?php

namespace App\Services;

use App\Enums\CropSeason;
use App\Enums\IrrigationSourceType;
use App\Models\CropPattern;
use App\Models\IrrigationRecord;
use App\Models\LandHolding;
use App\Models\LandInsight;
use App\Models\Region;
use App\Models\Well;
use Illuminate\Support\Facades\DB;

class LandInsightSyncService
{
    /** @var array<string, array{n: int, p: int, k: int, ph: float, rain: float}> */
    private const ZONE_SOIL_PROFILES = [
        'Indo-Gangetic Plain' => ['n' => 180, 'p' => 45, 'k' => 210, 'ph' => 7.2, 'rain' => 650],
        'Western Ghats Foothills' => ['n' => 140, 'p' => 60, 'k' => 320, 'ph' => 7.8, 'rain' => 750],
        'Cauvery Basin' => ['n' => 160, 'p' => 35, 'k' => 190, 'ph' => 6.5, 'rain' => 850],
        'Thar Fringe' => ['n' => 90, 'p' => 25, 'k' => 280, 'ph' => 8.4, 'rain' => 280],
        'Krishna Delta' => ['n' => 210, 'p' => 55, 'k' => 240, 'ph' => 7.4, 'rain' => 980],
        'Malwa Plateau' => ['n' => 155, 'p' => 40, 'k' => 200, 'ph' => 7.5, 'rain' => 1050],
        'Deccan Plateau' => ['n' => 130, 'p' => 35, 'k' => 180, 'ph' => 7.3, 'rain' => 700],
        'North Eastern Plains' => ['n' => 170, 'p' => 50, 'k' => 190, 'ph' => 6.8, 'rain' => 1500],
        'Eastern Ghats Coastal Plains' => ['n' => 150, 'p' => 40, 'k' => 210, 'ph' => 6.9, 'rain' => 1050],
        'Saurashtra Plains' => ['n' => 120, 'p' => 30, 'k' => 175, 'ph' => 7.2, 'rain' => 650],
        'Tapi Basin' => ['n' => 140, 'p' => 35, 'k' => 180, 'ph' => 7.1, 'rain' => 900],
        'Coastal Plains' => ['n' => 165, 'p' => 45, 'k' => 210, 'ph' => 6.8, 'rain' => 1200],
        'Himalayan Foothills' => ['n' => 110, 'p' => 30, 'k' => 190, 'ph' => 6.4, 'rain' => 1750],
        'North Eastern Hills' => ['n' => 100, 'p' => 28, 'k' => 160, 'ph' => 5.9, 'rain' => 2000],
        'Chhattisgarh Plains' => ['n' => 170, 'p' => 45, 'k' => 220, 'ph' => 6.8, 'rain' => 1300],
        'Aravalli Hills' => ['n' => 95, 'p' => 25, 'k' => 170, 'ph' => 8.0, 'rain' => 550],
        'Western Coastal Plains' => ['n' => 155, 'p' => 40, 'k' => 200, 'ph' => 6.7, 'rain' => 1600],
    ];

    public function syncAll(?int $year = null): int
    {
        $year ??= (int) date('Y');
        $count = 0;

        Region::query()
            ->districts()
            ->each(function (Region $district) use ($year, &$count) {
                $insight = $this->buildForDistrict($district, $year);

                if ($insight === null) {
                    return;
                }

                LandInsight::updateOrCreate(
                    ['region_id' => $district->id],
                    $insight
                );

                $count++;
            });

        return $count;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildForDistrict(Region $district, int $year): ?array
    {
        $regionId = $district->id;

        $holdingAvg = (float) LandHolding::query()
            ->where('region_id', $regionId)
            ->avg('area_hectares');

        if ($holdingAvg <= 0) {
            return null;
        }

        $topCrops = $this->topCropsForDistrict($regionId, $year);

        $dominantSeason = CropPattern::query()
            ->where('region_id', $regionId)
            ->where('year', $year)
            ->select('season', DB::raw('COUNT(*) as cnt'))
            ->groupBy('season')
            ->orderByDesc('cnt')
            ->value('season');

        $primarySource = IrrigationRecord::query()
            ->where('region_id', $regionId)
            ->select('source_type', DB::raw('COUNT(*) as cnt'))
            ->groupBy('source_type')
            ->orderByDesc('cnt')
            ->value('source_type');

        $avgWellDepth = (int) round((float) Well::query()->where('region_id', $regionId)->avg('depth_feet'));

        $soil = self::ZONE_SOIL_PROFILES[$district->agricultural_zone ?? ''] ?? [
            'n' => 150, 'p' => 40, 'k' => 200, 'ph' => 7.0, 'rain' => 800,
        ];

        return [
            'holding_size_avg' => round($holdingAvg, 2),
            'primary_irrigation_source' => $this->irrigationLabel($primarySource),
            'cropping_pattern_type' => $this->croppingPatternLabel($dominantSeason, count($topCrops)),
            'major_crops' => $topCrops ?: ['—'],
            'avg_well_depth' => $avgWellDepth,
            'soil_ph' => $soil['ph'],
            'nitrogen_level' => $soil['n'],
            'phosphorus_level' => $soil['p'],
            'potassium_level' => $soil['k'],
            'avg_rainfall' => $soil['rain'],
            'current_market_price' => $this->marketPriceForCrops($topCrops),
        ];
    }

    /**
     * @return list<string>
     */
    private function topCropsForDistrict(int $regionId, int $year): array
    {
        $topCrops = CropPattern::query()
            ->where('region_id', $regionId)
            ->where('year', $year)
            ->join('crops', 'crops.id', '=', 'crop_patterns.crop_id')
            ->select('crops.name', DB::raw('SUM(crop_patterns.area_hectares) as total_area'))
            ->groupBy('crops.id', 'crops.name')
            ->orderByDesc('total_area')
            ->limit(3)
            ->pluck('crops.name')
            ->all();

        if ($topCrops !== []) {
            return $topCrops;
        }

        return CropPattern::query()
            ->where('region_id', $regionId)
            ->join('crops', 'crops.id', '=', 'crop_patterns.crop_id')
            ->select('crops.name', DB::raw('SUM(crop_patterns.area_hectares) as total_area'))
            ->groupBy('crops.id', 'crops.name')
            ->orderByDesc('total_area')
            ->limit(3)
            ->pluck('crops.name')
            ->all();
    }

    private function irrigationLabel(mixed $source): string
    {
        if ($source instanceof IrrigationSourceType) {
            return $source->label();
        }

        if (is_string($source)) {
            try {
                return IrrigationSourceType::from($source)->label();
            } catch (\ValueError) {
                return ucwords(str_replace('_', ' ', $source));
            }
        }

        return 'Not recorded';
    }

    private function marketPriceForCrops(array $topCrops): int
    {
        $prices = [
            'Wheat' => 2200,
            'Rice (Paddy)' => 2500,
            'Maize' => 1900,
            'Cotton' => 4500,
            'Sugarcane' => 3200,
            'Soybean' => 4100,
            'Groundnut' => 5200,
            'Mustard' => 6200,
            'Potato' => 4500,
            'Onion' => 7600,
            'Mango' => 8800,
            'Banana' => 2800,
            'Chickpea' => 6500,
            'Turmeric' => 42000,
            'Tea' => 3500,
            'Sesame' => 12000,
            'Coconut' => 9400,
            'Pepper' => 85000,
            'Ginger' => 32000,

        ];

        foreach ($topCrops as $crop) {
            if (isset($prices[$crop])) {
                return $prices[$crop];
            }
        }

        return 0;
    }

    private function croppingPatternLabel(mixed $season, int $cropCount): string
    {
        $seasonLabel = $season instanceof CropSeason
            ? ucfirst($season->value)
            : ($season ? ucfirst((string) $season) : 'Mixed');

        if ($cropCount >= 3) {
            return "{$seasonLabel}-led multi-crop rotation";
        }

        return "{$seasonLabel}-season cultivation";
    }
}
