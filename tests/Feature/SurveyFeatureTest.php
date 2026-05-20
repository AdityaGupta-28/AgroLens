<?php

use App\Enums\RegionType;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Region;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Database\Seeders\CropSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(CropSeeder::class);
});

test('government officer can view surveys listing', function () {
    $officer = User::factory()->governmentOfficer()->create();

    // Seed default surveys
    $survey = Survey::create([
        'title' => 'Test Sowing Survey',
        'code' => 'TEST-SOW-26',
        'schema' => ['fields' => ['crop_id', 'area_hectares']],
        'is_active' => true,
    ]);

    $response = $this->actingAs($officer)
        ->get(route('surveys.index'));

    $response->assertStatus(200);
    $response->assertSee('Test Sowing Survey');
    $response->assertSee('TEST-SOW-26');
});

test('public viewer cannot access surveys listing', function () {
    $viewer = User::factory()->publicViewer()->create();

    $response = $this->actingAs($viewer)
        ->get(route('surveys.index'));

    $response->assertStatus(403);
});

test('government officer can create a custom survey campaign', function () {
    $officer = User::factory()->governmentOfficer()->create();

    $response = $this->actingAs($officer)
        ->get(route('surveys.create'));

    $response->assertStatus(200);

    $postData = [
        'title' => 'Custom Soil Nutrition Campaign 2026',
        'code' => 'SOIL-NUTRI-26',
        'description' => 'A visual testing campaign for nitrogen levels.',
        'starts_at' => '2026-05-18',
        'ends_at' => '2026-12-31',
        'is_active' => '1',
        'fields' => ['crop_id', 'area_hectares', 'season'],
    ];

    $postResponse = $this->actingAs($officer)
        ->post(route('surveys.store'), $postData);

    $postResponse->assertRedirect(route('surveys.index'));
    $this->assertDatabaseHas('surveys', [
        'code' => 'SOIL-NUTRI-26',
        'title' => 'Custom Soil Nutrition Campaign 2026',
    ]);

    $survey = Survey::where('code', 'SOIL-NUTRI-26')->first();
    expect($survey->schema['fields'])->toBe(['crop_id', 'area_hectares', 'season']);
});

test('public viewer cannot create a survey campaign', function () {
    $viewer = User::factory()->publicViewer()->create();

    $postData = [
        'title' => 'Unauthorized Campaign',
        'code' => 'UNAUTH-26',
        'fields' => ['crop_id'],
    ];

    $response = $this->actingAs($viewer)
        ->post(route('surveys.store'), $postData);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('surveys', ['code' => 'UNAUTH-26']);
});

test('government officer can collect and submit survey response data', function () {
    $officer = User::factory()->governmentOfficer()->create();

    $survey = Survey::create([
        'title' => 'Crop Census 2026',
        'code' => 'CENSUS-2026',
        'schema' => ['fields' => ['crop_id', 'area_hectares', 'season', 'gps_coordinates']],
        'is_active' => true,
    ]);

    $crop = Crop::first();
    $region = Region::create([
        'name' => 'Ferozepur',
        'type' => RegionType::District,
        'state' => 'Punjab',
        'code' => 'FZP',
    ]);

    $farmer = Farmer::create([
        'farmer_code' => 'FZP-00001',
        'name' => 'Aditya Singh',
        'region_id' => $region->id,
        'ownership_type' => 'owner',
    ]);

    $response = $this->actingAs($officer)
        ->get(route('surveys.collect', $survey));

    $response->assertStatus(200);

    $submitData = [
        'farmer_name' => $farmer->name,
        'region_id' => $region->id,
        'latitude' => '30.9124',
        'longitude' => '74.7873',
        'responses' => [
            'crop_id' => $crop->id,
            'area_hectares' => '5.75',
            'season' => 'Kharif',
        ],
    ];

    $submitResponse = $this->actingAs($officer)
        ->post(route('surveys.submit', $survey), $submitData);

    $submitResponse->assertRedirect(route('surveys.index'));
    $this->assertDatabaseHas('survey_responses', [
        'survey_id' => $survey->id,
        'farmer_id' => $farmer->id,
        'region_id' => $region->id,
        'latitude' => '30.9124',
        'longitude' => '74.7873',
        'status' => 'submitted',
    ]);

    $surveyResponse = SurveyResponse::where('survey_id', $survey->id)->first();
    expect((int) $surveyResponse->responses['crop_id'])->toBe($crop->id)
        ->and((float) $surveyResponse->responses['area_hectares'])->toBe(5.75)
        ->and($surveyResponse->responses['season'])->toBe('Kharif');
});

test('government officer can view analytics dashboard of a survey campaign', function () {
    $officer = User::factory()->governmentOfficer()->create();

    $survey = Survey::create([
        'title' => 'Test Sowing Survey 2',
        'code' => 'TEST-SOW-2',
        'schema' => ['fields' => ['crop_id', 'area_hectares']],
        'is_active' => true,
    ]);

    $response = $this->actingAs($officer)
        ->get(route('surveys.responses', $survey));

    $response->assertStatus(200);
    $response->assertSee('Campaign Submissions & Analytics');
});

test('government officer can delete a response and delete a survey', function () {
    $officer = User::factory()->governmentOfficer()->create();

    $survey = Survey::create([
        'title' => 'Survey to Delete',
        'code' => 'DEL-26',
        'schema' => ['fields' => ['well_type']],
        'is_active' => true,
    ]);

    $surveyResponse = SurveyResponse::create([
        'survey_id' => $survey->id,
        'enumerator_id' => $officer->id,
        'responses' => ['well_type' => 'bore_well'],
        'status' => 'submitted',
    ]);

    // 1. Delete response
    $delResp = $this->actingAs($officer)
        ->delete(route('surveys.destroyResponse', $surveyResponse));

    $delResp->assertRedirect();
    $this->assertSoftDeleted('survey_responses', ['id' => $surveyResponse->id]);

    // 2. Delete survey
    $delSurvey = $this->actingAs($officer)
        ->delete(route('surveys.destroy', $survey));

    $delSurvey->assertRedirect(route('surveys.index'));
    $this->assertSoftDeleted('surveys', ['id' => $survey->id]);
});
