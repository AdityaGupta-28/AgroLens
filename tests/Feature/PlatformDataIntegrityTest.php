<?php

use App\Models\Crop;
use App\Models\LandHolding;
use App\Models\LandInsight;
use App\Models\User;
use App\Repositories\AnalyticsRepository;
use App\Services\LandInsightSyncService;
use Database\Seeders\AgroLensPlatformSeeder;
use Database\Seeders\CropSeeder;
use Database\Seeders\LandInsightSeeder;

beforeEach(function () {
    $this->seed(CropSeeder::class);
    $this->seed(AgroLensPlatformSeeder::class);
    $this->seed(LandInsightSeeder::class);
});

test('crop pattern areas match land holding sizes', function () {
    $mismatches = 0;

    LandHolding::query()->with('cropPatterns')->each(function (LandHolding $holding) use (&$mismatches) {
        if ($holding->cropPatterns->isEmpty()) {
            return;
        }

        $sum = round((float) $holding->cropPatterns->sum('area_hectares'), 4);
        $area = round((float) $holding->area_hectares, 4);

        if (abs($sum - $area) > 0.05) {
            $mismatches++;
        }
    });

    expect($mismatches)->toBe(0);
});

test('crop distribution percentages sum to one hundred', function () {
    $year = (int) date('Y');
    $crops = app(AnalyticsRepository::class)->getCropDistribution(null, null, null, $year);

    expect(collect($crops)->sum('percentage'))->toBeGreaterThanOrEqual(99.0)
        ->toBeLessThanOrEqual(100.1);
});

test('land insights major crops exist in crop catalog', function () {
    $catalog = Crop::pluck('name')->all();

    LandInsight::all()->each(function (LandInsight $insight) use ($catalog) {
        foreach ($insight->major_crops as $crop) {
            if ($crop !== '—') {
                expect($catalog)->toContain($crop);
            }
        }
    });
});

test('land insights match operational sync output', function () {
    $year = (int) date('Y');
    $sync = app(LandInsightSyncService::class);

    LandInsight::with('region')->get()->each(function (LandInsight $insight) use ($sync, $year) {
        $built = $sync->buildForDistrict($insight->region, $year);
        expect($built)->not->toBeNull()
            ->and($built['major_crops'])->toBe($insight->major_crops);
    });
});

test('government officer can access standard officer pages but not admin', function () {
    $user = User::factory()->governmentOfficer()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('land-insights.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('surveys.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('admin.index'))
        ->assertForbidden();
});

test('super admin can access admin dashboard', function () {
    $user = User::factory()->superAdmin()->create();

    $this->actingAs($user)
        ->get(route('admin.index'))
        ->assertOk();
});

test('gis markers filter by state and district', function () {
    $year = (int) date('Y');
    $repo = app(AnalyticsRepository::class);

    $punjabMarkers = collect($repo->getMapMarkers('Punjab', $year));
    expect($punjabMarkers)->not->toBeEmpty()
        ->and($punjabMarkers->pluck('state')->unique()->values()->all())->toBe(['Punjab']);

    $districtId = (int) $punjabMarkers->first()['id'];
    $districtMarkers = $repo->getMapMarkers('Punjab', $year, $districtId);

    expect($districtMarkers)->toHaveCount(1)
        ->and($districtMarkers[0]['id'])->toBe($districtId);
});

test('public viewer cannot access admin but can access dashboard', function () {
    $user = User::factory()->publicViewer()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('admin.index'))
        ->assertForbidden();
});
