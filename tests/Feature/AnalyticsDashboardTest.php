<?php

use App\Enums\UserRole;
use App\Livewire\AnalyticsDashboard;
use App\Models\Crop;
use App\Models\CropPattern;
use App\Models\Farmer;
use App\Models\LandHolding;
use App\Models\Region;
use App\Models\User;
use App\Repositories\AnalyticsRepository;
use App\Services\AnalyticsService;
use Database\Seeders\CropSeeder;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(CropSeeder::class);
});

test('analytics repository filters crop data by state and year', function () {
    $crop = Crop::first();
    $punjab = Region::create(['name' => 'Punjab', 'type' => 'state', 'state' => 'Punjab', 'code' => 'PB']);
    $maharashtra = Region::create(['name' => 'Maharashtra', 'type' => 'state', 'state' => 'Maharashtra', 'code' => 'MH']);
    $amritsar = Region::create(['name' => 'Amritsar', 'type' => 'district', 'state' => 'Punjab', 'code' => 'AMR', 'parent_id' => $punjab->id]);
    $nashik = Region::create(['name' => 'Nashik', 'type' => 'district', 'state' => 'Maharashtra', 'code' => 'NSK', 'parent_id' => $maharashtra->id]);

    foreach ([$amritsar, $nashik] as $district) {
        $farmer = Farmer::create([
            'farmer_code' => 'T-'.$district->code,
            'name' => 'Test Farmer',
            'region_id' => $district->id,
        ]);

        $holding = LandHolding::create([
            'farmer_id' => $farmer->id,
            'region_id' => $district->id,
            'area_hectares' => 5,
            'category' => 'small',
            'is_irrigated' => true,
        ]);

        CropPattern::create([
            'region_id' => $district->id,
            'crop_id' => $crop->id,
            'land_holding_id' => $holding->id,
            'season' => 'kharif',
            'year' => 2026,
            'area_hectares' => 5,
        ]);
    }

    $repo = app(AnalyticsRepository::class);

    $punjabCrops = $repo->getCropDistribution(null, 'Punjab', null, 2026);
    $allCrops = $repo->getCropDistribution(null, null, null, 2026);

    expect($punjabCrops)->toHaveCount(1)
        ->and(collect($punjabCrops)->first()['area'])->toBe(5.0)
        ->and(collect($allCrops)->first()['area'])->toBe(10.0)
        ->and(collect($allCrops)->first())->toHaveKeys(['crop', 'area', 'percentage', 'color']);
});

test('analytics dashboard livewire applies state filter to charts', function () {
    $user = User::factory()->create([
        'role' => UserRole::GovernmentOfficer,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(AnalyticsDashboard::class)
        ->set('state', 'Punjab')
        ->assertSet('state', 'Punjab')
        ->assertOk();
});

test('analytics service passes year filter to repository', function () {
    $service = app(AnalyticsService::class);

    $data = $service->dashboardData([
        'year' => (int) date('Y'),
    ]);

    expect($data)->toHaveKeys([
        'kpis',
        'holding_distribution',
        'irrigation_breakdown',
        'crop_distribution',
        'well_depths',
        'map_markers',
    ]);
});
