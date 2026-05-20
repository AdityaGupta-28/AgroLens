<?php

namespace App\Console\Commands;

use App\Services\LandInsightSyncService;
use Illuminate\Console\Command;

class SyncLandInsightsCommand extends Command
{
    protected $signature = 'agrolens:sync-land-insights {--year= : Crop year for sowing records}';

    protected $description = 'Rebuild district land insights from operational agricultural data';

    public function handle(LandInsightSyncService $sync): int
    {
        $year = $this->option('year') ? (int) $this->option('year') : (int) date('Y');
        $count = $sync->syncAll($year);

        $this->info("Synced land insights for {$count} districts (year {$year}).");

        return self::SUCCESS;
    }
}
