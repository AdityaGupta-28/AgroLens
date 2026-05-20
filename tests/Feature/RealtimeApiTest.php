<?php

use App\Services\RealtimeAnalyticsService;
use Database\Seeders\AgroLensPlatformSeeder;
use Database\Seeders\CropSeeder;

beforeEach(function () {
    $this->seed(CropSeeder::class);
    $this->seed(AgroLensPlatformSeeder::class);
});

test('dashboard analytics use operational database records', function () {
    $data = app(RealtimeAnalyticsService::class)->dashboardData(['year' => (int) date('Y')]);

    expect($data['data_source'])->toBe('database')
        ->and($data['kpis']['total_farmers'])->toBeGreaterThan(0)
        ->and($data['crop_distribution'])->not->toBeEmpty()
        ->and(collect($data['crop_distribution'])->sum('percentage'))->toBeGreaterThanOrEqual(99.0);
});
