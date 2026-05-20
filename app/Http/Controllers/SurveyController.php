<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Region;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyController extends Controller
{
    /**
     * Display a listing of surveys.
     */
    public function index(): View
    {
        $surveys = Survey::query()
            ->withCount('responses')
            ->orderByDesc('is_active')
            ->orderBy('title')
            ->get();

        return view('surveys.index', [
            'surveys' => $surveys,
            'totalResponses' => SurveyResponse::count(),
        ]);
    }

    /**
     * Show the form for creating a new survey.
     */
    public function create(): View
    {
        $fields = [
            'crop_id' => 'Crop Sown Selection',
            'area_hectares' => 'Cultivated Area (Hectares)',
            'season' => 'Sowing Season',
            'irrigation_source' => 'Irrigation Water Source',
            'gps_coordinates' => 'Field GPS Coordinates',
            'well_type' => 'Groundwater Well Type',
            'depth_feet' => 'Well Depth (Feet)',
            'water_table_level_m' => 'Water Table Level (Meters)',
            'recharge_status' => 'Recharge Status',
        ];

        return view('surveys.create', compact('fields'));
    }

    /**
     * Store a newly created survey.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:surveys,code',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'fields' => 'required|array|min:1',
            'fields.*' => 'string|in:crop_id,area_hectares,season,irrigation_source,gps_coordinates,well_type,depth_feet,water_table_level_m,recharge_status',
        ]);

        Survey::create([
            'title' => $request->title,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'schema' => [
                'fields' => $request->fields,
            ],
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('surveys.index')->with('success', 'Survey campaign created successfully.');
    }

    /**
     * Show the form to collect data for a survey.
     */
    public function collect(Survey $survey): View
    {
        $crops = Crop::orderBy('name')->get();
        $farmers = Farmer::orderBy('name')->get();
        $regions = Region::districts()->orderBy('name')->get();

        return view('surveys.collect', compact('survey', 'crops', 'farmers', 'regions'));
    }

    /**
     * Submit a survey response.
     */
    public function submit(Request $request, Survey $survey): RedirectResponse
    {
        $fields = $survey->schema['fields'] ?? [];

        // Build dynamic validation rules
        $rules = [
            'farmer_name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];

        foreach ($fields as $field) {
            if ($field === 'crop_id') {
                $rules['responses.crop_id'] = 'required|exists:crops,id';
            } elseif ($field === 'area_hectares') {
                $rules['responses.area_hectares'] = 'required|numeric|min:0.01';
            } elseif ($field === 'season') {
                $rules['responses.season'] = 'required|string|in:Kharif,Rabi,Zaid';
            } elseif ($field === 'irrigation_source') {
                $rules['responses.irrigation_source'] = 'required|string|in:bore_well,canal,dug_well,rain_fed,river,tank_pond';
            } elseif ($field === 'well_type') {
                $rules['responses.well_type'] = 'required|string|in:bore_well,tube_well,dug_well';
            } elseif ($field === 'depth_feet') {
                $rules['responses.depth_feet'] = 'required|integer|min:1';
            } elseif ($field === 'water_table_level_m') {
                $rules['responses.water_table_level_m'] = 'required|numeric|min:0';
            } elseif ($field === 'recharge_status') {
                $rules['responses.recharge_status'] = 'required|string|in:good,moderate,poor';
            }
        }

        $validated = $request->validate($rules);

        // Find or create farmer based on typed name
        $farmer = Farmer::where('name', $request->farmer_name)->first();
        if (! $farmer) {
            $farmer = Farmer::create([
                'name' => $request->farmer_name,
                'farmer_code' => 'F-' . strtoupper(bin2hex(random_bytes(3))),
                'region_id' => $request->region_id,
                'enumerator_id' => auth()->id(),
            ]);
        }

        // Fetch coordinates from responses if present
        $lat = $request->latitude;
        $lng = $request->longitude;

        if (in_array('gps_coordinates', $fields)) {
            $lat = $request->input('responses.latitude') ?? $lat;
            $lng = $request->input('responses.longitude') ?? $lng;
        }

        $responses = $request->input('responses', []);

        // Create the raw SurveyResponse record
        SurveyResponse::create([
            'survey_id' => $survey->id,
            'enumerator_id' => auth()->id(),
            'region_id' => $request->region_id,
            'farmer_id' => $farmer->id,
            'responses' => $responses,
            'status' => 'submitted',
            'latitude' => $lat,
            'longitude' => $lng,
            'submitted_at' => now(),
        ]);

        // Create matching operational data in primary tables to reflect instantly on dashboard
        $area = isset($responses['area_hectares']) ? (float) $responses['area_hectares'] : 1.5;
        
        $category = \App\Enums\LandHoldingCategory::Marginal;
        if ($area >= 10.0) {
            $category = \App\Enums\LandHoldingCategory::Large;
        } elseif ($area >= 4.0) {
            $category = \App\Enums\LandHoldingCategory::Medium;
        } elseif ($area >= 2.0) {
            $category = \App\Enums\LandHoldingCategory::SemiMedium;
        } elseif ($area >= 1.0) {
            $category = \App\Enums\LandHoldingCategory::Small;
        }

        $holding = \App\Models\LandHolding::create([
            'farmer_id' => $farmer->id,
            'region_id' => $request->region_id,
            'survey_number' => 'SRV-' . strtoupper(bin2hex(random_bytes(2))),
            'area_hectares' => $area,
            'category' => $category,
            'is_irrigated' => isset($responses['irrigation_source']) && $responses['irrigation_source'] !== 'rain_fed',
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        if (isset($responses['crop_id']) || isset($responses['season'])) {
            $cropId = $responses['crop_id'] ?? \App\Models\Crop::first()?->id;
            $season = isset($responses['season']) ? strtolower($responses['season']) : 'kharif';
            if ($cropId) {
                \App\Models\CropPattern::create([
                    'region_id' => $request->region_id,
                    'crop_id' => $cropId,
                    'land_holding_id' => $holding->id,
                    'season' => $season,
                    'year' => (int) date('Y'),
                    'area_hectares' => $area,
                    'yield_quintals' => $area * 25.0,
                    'rotation_group' => 'A',
                    'fertilizer_usage_kg' => $area * 120.0,
                    'irrigation_dependent' => isset($responses['irrigation_source']) && $responses['irrigation_source'] !== 'rain_fed',
                ]);
            }
        }

        if (isset($responses['irrigation_source'])) {
            \App\Models\IrrigationRecord::create([
                'land_holding_id' => $holding->id,
                'region_id' => $request->region_id,
                'source_type' => $responses['irrigation_source'],
                'water_availability_score' => 4.0,
                'seasonal_usage' => 'Medium',
                'efficiency_percent' => 75.0,
                'water_stress' => false,
                'groundwater_level_m' => isset($responses['water_table_level_m']) ? (float) $responses['water_table_level_m'] : null,
            ]);
        }

        if (isset($responses['well_type']) || isset($responses['depth_feet'])) {
            \App\Models\Well::create([
                'region_id' => $request->region_id,
                'land_holding_id' => $holding->id,
                'well_type' => $responses['well_type'] ?? 'bore_well',
                'depth_feet' => isset($responses['depth_feet']) ? (int) $responses['depth_feet'] : 150,
                'water_table_level_m' => isset($responses['water_table_level_m']) ? (float) $responses['water_table_level_m'] : 15.0,
                'recharge_status' => $responses['recharge_status'] ?? 'moderate',
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        }

        // Call LandInsightSyncService to synchronize analytics and region profiles immediately
        try {
            resolve(\App\Services\LandInsightSyncService::class)->syncAll((int) date('Y'));
        } catch (\Exception $e) {
            // Log or ignore to avoid blocking submission
        }

        return redirect()->route('surveys.index')->with('success', 'Survey response collected successfully.');
    }

    /**
     * View responses and statistical analytics for a survey.
     */
    public function responses(Survey $survey): View
    {
        $responses = $survey->responses()
            ->with(['enumerator', 'region', 'farmer'])
            ->orderByDesc('submitted_at')
            ->paginate(10);

        $allResponses = $survey->responses;

        // Statistics calculators
        $cropCounts = [];
        $wellTypeCounts = [];
        $rechargeCounts = [];
        $totalArea = 0;
        $areaCount = 0;
        $totalDepth = 0;
        $depthCount = 0;

        $cropNames = Crop::pluck('name', 'id')->all();

        foreach ($allResponses as $r) {
            $data = $r->responses;
            if (! is_array($data)) {
                continue;
            }

            // Crop counts
            if (isset($data['crop_id'])) {
                $cId = $data['crop_id'];
                if (isset($cropNames[$cId])) {
                    $cName = $cropNames[$cId];
                    $cropCounts[$cName] = ($cropCounts[$cName] ?? 0) + 1;
                }
            }

            // Well type counts
            if (isset($data['well_type'])) {
                $wType = str_replace('_', ' ', ucwords($data['well_type'], '_'));
                $wellTypeCounts[$wType] = ($wellTypeCounts[$wType] ?? 0) + 1;
            }

            // Recharge status counts
            if (isset($data['recharge_status'])) {
                $status = ucfirst($data['recharge_status']);
                $rechargeCounts[$status] = ($rechargeCounts[$status] ?? 0) + 1;
            }

            // Hectares sum
            if (isset($data['area_hectares']) && is_numeric($data['area_hectares'])) {
                $totalArea += (float) $data['area_hectares'];
                $areaCount++;
            }

            // Depth sum
            if (isset($data['depth_feet']) && is_numeric($data['depth_feet'])) {
                $totalDepth += (float) $data['depth_feet'];
                $depthCount++;
            }
        }

        $avgArea = $areaCount > 0 ? round($totalArea / $areaCount, 2) : 0;
        $avgDepth = $depthCount > 0 ? round($totalDepth / $depthCount, 2) : 0;

        $stats = [
            'cropCounts' => $cropCounts,
            'wellTypeCounts' => $wellTypeCounts,
            'rechargeCounts' => $rechargeCounts,
            'totalArea' => round($totalArea, 2),
            'avgArea' => $avgArea,
            'avgDepth' => $avgDepth,
            'areaCount' => $areaCount,
            'depthCount' => $depthCount,
        ];

        return view('surveys.responses', compact('survey', 'responses', 'stats', 'cropNames'));
    }

    /**
     * Delete a survey campaign.
     */
    public function destroy(Survey $survey): RedirectResponse
    {
        $survey->delete();

        return redirect()->route('surveys.index')->with('success', 'Survey campaign deleted successfully.');
    }

    /**
     * Delete a survey response.
     */
    public function destroyResponse(SurveyResponse $response): RedirectResponse
    {
        $response->delete();

        return redirect()->back()->with('success', 'Survey response deleted successfully.');
    }
}
