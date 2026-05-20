<?php

namespace Database\Seeders;

use App\Services\LandInsightSyncService;
use Illuminate\Database\Seeder;

class LandInsightSeeder extends Seeder
{
    public function run(): void
    {
        app(LandInsightSyncService::class)->syncAll((int) date('Y'));
    }
}
