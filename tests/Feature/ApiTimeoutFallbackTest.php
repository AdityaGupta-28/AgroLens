<?php

use App\Services\AnalyticsService;
use Database\Seeders\AgroLensPlatformSeeder;
use Database\Seeders\CropSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(CropSeeder::class);
    $this->seed(AgroLensPlatformSeeder::class);
});

test('dashboard returns land insights data without external api', function () {
    $data = app(AnalyticsService::class)->dashboardData(['year' => (int) date('Y')]);

    expect($data['data_source'])->toBe('database')
        ->and($data)->toHaveKeys(['holding_distribution', 'irrigation_breakdown', 'crop_distribution', 'well_depths']);
});
