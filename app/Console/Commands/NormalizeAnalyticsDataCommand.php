<?php

namespace App\Console\Commands;

use App\Models\CropPattern;
use App\Models\LandHolding;
use App\Services\LandInsightSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NormalizeAnalyticsDataCommand extends Command
{
    protected $signature = 'agrolens:normalize-analytics {--year= : Set crop pattern year for records missing a valid year}';

    protected $description = 'Normalize crop pattern areas so they match land holding totals and align years';

    public function handle(): int
    {
        $targetYear = $this->option('year') ? (int) $this->option('year') : (int) date('Y');
        $fixed = 0;

        LandHolding::query()
            ->with('cropPatterns')
            ->chunkById(100, function ($holdings) use (&$fixed, $targetYear) {
                foreach ($holdings as $holding) {
                    $patterns = $holding->cropPatterns;

                    if ($patterns->isEmpty()) {
                        continue;
                    }

                    $totalPatternArea = (float) $patterns->sum('area_hectares');
                    $holdingArea = (float) $holding->area_hectares;

                    if ($totalPatternArea <= 0 || abs($totalPatternArea - $holdingArea) < 0.01) {
                        foreach ($patterns as $pattern) {
                            if (! $pattern->year) {
                                $pattern->update(['year' => $targetYear]);
                                $fixed++;
                            }
                        }

                        continue;
                    }

                    $factor = $holdingArea / $totalPatternArea;

                    foreach ($patterns as $pattern) {
                        $pattern->update([
                            'area_hectares' => round((float) $pattern->area_hectares * $factor, 4),
                            'year' => $pattern->year ?: $targetYear,
                        ]);
                        $fixed++;
                    }
                }
            });

        CropPattern::query()
            ->whereNull('year')
            ->orWhere('year', '<', 2018)
            ->update(['year' => $targetYear]);

        Cache::flush();
        DB::table('cache')->where('key', 'like', '%analytics.kpis%')->delete();

        $synced = app(LandInsightSyncService::class)->syncAll($targetYear);

        $this->info("Normalized {$fixed} crop pattern records. Target year: {$targetYear}.");
        $this->info("Synced land insights for {$synced} districts.");

        return self::SUCCESS;
    }
}
