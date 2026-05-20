<?php

namespace App\Console\Commands;

use App\Models\Crop;
use App\Models\CropPattern;
use App\Models\Farmer;
use App\Models\LandHolding;
use App\Models\LandInsight;
use App\Models\Region;
use App\Repositories\AnalyticsRepository;
use App\Services\LandInsightSyncService;
use Illuminate\Console\Command;

class VerifyAgroLensDataCommand extends Command
{
    protected $signature = 'agrolens:verify-data {--year= : Year to validate} {--fix : Re-normalize and sync land insights if issues found}';

    protected $description = 'Cross-check agricultural data integrity across dashboard, GIS, and land insights';

    public function handle(): int
    {
        $year = $this->option('year') ? (int) $this->option('year') : (int) date('Y');
        $issues = [];

        $this->info("AgroLens data verification (year {$year})");
        $this->newLine();

        $counts = [
            'Farmers' => Farmer::count(),
            'Land holdings' => LandHolding::count(),
            'Crop patterns' => CropPattern::count(),
            'Districts' => Region::districts()->count(),
            'Land insights' => LandInsight::count(),
            'Crops (catalog)' => Crop::count(),
        ];

        foreach ($counts as $label => $count) {
            $this->line(sprintf('  %-18s %s', $label.':', $count));
            if ($count === 0 && in_array($label, ['Farmers', 'Land holdings', 'Crop patterns', 'Districts'], true)) {
                $issues[] = "No {$label} — run: php artisan migrate:fresh --seed";
            }
        }

        $this->newLine();

        $holdingsMismatch = 0;
        LandHolding::query()->with('cropPatterns')->chunkById(100, function ($holdings) use (&$holdingsMismatch) {
            foreach ($holdings as $holding) {
                if ($holding->cropPatterns->isEmpty()) {
                    continue;
                }
                $sum = round((float) $holding->cropPatterns->sum('area_hectares'), 4);
                $area = round((float) $holding->area_hectares, 4);
                if (abs($sum - $area) > 0.05) {
                    $holdingsMismatch++;
                }
            }
        });

        if ($holdingsMismatch > 0) {
            $issues[] = "{$holdingsMismatch} holdings have crop areas that do not match parcel size";
        }
        $this->line('  Holdings area match: '.($holdingsMismatch === 0 ? 'OK' : "FAIL ({$holdingsMismatch} mismatches)"));

        $invalidCrops = CropPattern::query()
            ->whereNotIn('crop_id', Crop::pluck('id'))
            ->count();
        if ($invalidCrops > 0) {
            $issues[] = "{$invalidCrops} crop patterns reference missing crops";
        }
        $this->line('  Crop FK integrity: '.($invalidCrops === 0 ? 'OK' : "FAIL ({$invalidCrops})"));

        $repo = app(AnalyticsRepository::class);
        $kpiLand = $repo->getDashboardKpis(null, null, $year)['total_cultivated_land'];
        $cropTotal = collect($repo->getCropDistribution(null, null, null, $year))->sum('area');
        $patternTotal = (float) CropPattern::query()->where('year', $year)->sum('area_hectares');

        $cropKpiDelta = abs($cropTotal - $kpiLand);
        if ($cropKpiDelta > max(1, $kpiLand * 0.02)) {
            $issues[] = sprintf(
                'Crop chart total (%.2f ha) diverges from KPI cultivated land (%.2f ha)',
                $cropTotal,
                $kpiLand
            );
        }
        $this->line(sprintf('  KPI cultivated:     %.2f ha', $kpiLand));
        $this->line(sprintf('  Pie chart total:    %.2f ha (delta %.2f)', $cropTotal, $cropKpiDelta));
        $this->line(sprintf('  Pattern records:    %.2f ha', $patternTotal));

        $pctSum = collect($repo->getCropDistribution(null, null, null, $year))->sum('percentage');
        $this->line('  Pie percentages:    '.round($pctSum, 1).'% '.(abs($pctSum - 100) < 0.5 || $cropTotal == 0 ? 'OK' : 'WARN'));

        $markers = $repo->getMapMarkers(null, $year);
        $missingCoords = Region::districts()->whereNull('latitude')->count();
        if ($missingCoords > 0) {
            $issues[] = "{$missingCoords} districts missing map coordinates";
        }
        $this->line('  GIS markers:        '.count($markers).' districts, coords '.($missingCoords === 0 ? 'OK' : 'FAIL'));

        $sync = app(LandInsightSyncService::class);
        $insightDrift = 0;
        LandInsight::query()->with('region')->each(function (LandInsight $insight) use ($sync, $year, &$insightDrift) {
            $built = $sync->buildForDistrict($insight->region, $year);
            if ($built === null) {
                return;
            }
            if ($built['major_crops'] !== $insight->major_crops) {
                $insightDrift++;
            }
        });

        if ($insightDrift > 0) {
            $issues[] = "{$insightDrift} land insights out of sync with operational data — run: php artisan agrolens:sync-land-insights";
        }
        $this->line('  Land insight sync:  '.($insightDrift === 0 ? 'OK' : "FAIL ({$insightDrift} stale)"));

        $unknownInsightCrops = LandInsight::query()->get()->flatMap(fn ($i) => $i->major_crops)
            ->unique()
            ->filter(fn ($name) => $name !== '—' && ! Crop::where('name', $name)->exists());
        if ($unknownInsightCrops->isNotEmpty()) {
            $issues[] = 'Land insights list crops not in catalog: '.$unknownInsightCrops->implode(', ');
        }
        $this->line('  Crop name catalog:  '.($unknownInsightCrops->isEmpty() ? 'OK' : 'FAIL'));

        $this->newLine();

        if ($issues === []) {
            $this->info('All checks passed. Data is consistent across dashboard, GIS, and land insights.');

            return self::SUCCESS;
        }

        $this->error('Found '.count($issues).' issue(s):');
        foreach ($issues as $issue) {
            $this->line("  • {$issue}");
        }

        if ($this->option('fix')) {
            $this->newLine();
            $this->call('agrolens:normalize-analytics', ['--year' => $year]);
            $this->call('agrolens:sync-land-insights', ['--year' => $year]);
            $this->info('Repair complete. Re-run agrolens:verify-data to confirm.');
        } else {
            $this->line('');
            $this->line('Run with --fix to normalize crop areas and re-sync land insights.');
        }

        return self::FAILURE;
    }
}
