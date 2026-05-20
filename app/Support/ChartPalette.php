<?php

namespace App\Support;

final class ChartPalette
{
    /** @var array<string, string> */
    private const CROP_COLORS = [
        'Wheat' => '#eab308',
        'Rice (Paddy)' => '#16a34a',
        'Cotton' => '#cbd5e1',
        'Sugarcane' => '#84cc16',
        'Grapes' => '#a855f7',
    ];

    private const FALLBACK = [
        '#0ea5e9', '#f97316', '#ec4899', '#14b8a6', '#6366f1',
        '#ef4444', '#8b5cf6', '#22c55e', '#f59e0b', '#64748b',
    ];

    /** @var array<string, string> */
    private const HOLDING_COLORS = [
        'marginal' => '#38bdf8',
        'small' => '#10b981',
        'semi_medium' => '#f59e0b',
        'medium' => '#8b5cf6',
        'large' => '#ef4444',
    ];

    /** @var array<string, string> */
    private const IRRIGATION_COLORS = [
        'canal' => '#0ea5e9',
        'tube_well' => '#6366f1',
        'dug_well' => '#14b8a6',
        'bore_well' => '#8b5cf6',
        'rain_fed' => '#f59e0b',
        'river' => '#22c55e',
        'tank_pond' => '#ec4899',
    ];

    /**
     * @param  list<string>  $cropNames
     * @return list<string>
     */
    public static function forCrops(array $cropNames): array
    {
        return array_map(
            fn (string $name, int $i) => self::CROP_COLORS[$name] ?? self::FALLBACK[$i % count(self::FALLBACK)],
            $cropNames,
            array_keys($cropNames)
        );
    }

    public static function cropColor(string $cropName, int $index = 0): string
    {
        return self::CROP_COLORS[$cropName] ?? self::FALLBACK[$index % count(self::FALLBACK)];
    }

    public static function holdingColor(string $category): string
    {
        return self::HOLDING_COLORS[$category] ?? '#64748b';
    }

    public static function irrigationColor(string $source): string
    {
        return self::IRRIGATION_COLORS[$source] ?? '#64748b';
    }

    /**
     * @return list<string>
     */
    public static function mapMarkerGradient(): array
    {
        return ['#fef3c7', '#fcd34d', '#f59e0b', '#ea580c', '#dc2626'];
    }

    public static function mapMarkerColor(int $farmers, int $maxFarmers): string
    {
        $palette = self::mapMarkerGradient();
        if ($maxFarmers <= 0) {
            return $palette[0];
        }

        $index = (int) min(count($palette) - 1, floor(($farmers / $maxFarmers) * (count($palette) - 1)));

        return $palette[$index];
    }
}
