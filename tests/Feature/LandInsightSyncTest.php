<?php

use App\Models\Crop;
use App\Models\CropPattern;
use App\Models\Farmer;
use App\Models\LandHolding;
use App\Models\LandInsight;
use App\Models\Region;
use App\Services\LandInsightSyncService;
use Database\Seeders\CropSeeder;

beforeEach(function () {
    $this->seed(CropSeeder::class);
});

test('land insight sync derives major crops from crop patterns', function () {
    $district = Region::create([
        'name' => 'Test District',
        'type' => 'district',
        'state' => 'Punjab',
        'code' => 'TST',
        'agricultural_zone' => 'Indo-Gangetic Plain',
    ]);

    $crop = Crop::where('name', 'Wheat')->first();
    $farmer = Farmer::create([
        'farmer_code' => 'TST-001',
        'name' => 'Test Farmer',
        'region_id' => $district->id,
    ]);

    $holding = LandHolding::create([
        'farmer_id' => $farmer->id,
        'region_id' => $district->id,
        'area_hectares' => 4,
        'category' => 'small',
        'is_irrigated' => true,
    ]);

    CropPattern::create([
        'region_id' => $district->id,
        'crop_id' => $crop->id,
        'land_holding_id' => $holding->id,
        'season' => 'rabi',
        'year' => 2026,
        'area_hectares' => 4,
        'yield_quintals' => 20,
    ]);

    app(LandInsightSyncService::class)->syncAll(2026);

    $insight = LandInsight::where('region_id', $district->id)->first();

    expect($insight)->not->toBeNull()
        ->and($insight->major_crops)->toContain('Wheat')
        ->and($insight->holding_size_avg)->toBe(4.0);
});
