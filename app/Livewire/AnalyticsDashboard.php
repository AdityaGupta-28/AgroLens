<?php

namespace App\Livewire;

use App\Enums\IrrigationSourceType;
use App\Models\Region;
use App\Services\AnalyticsService;
use Livewire\Attributes\On;
use Livewire\Component;

class AnalyticsDashboard extends Component
{
    public ?int $regionId = null;

    public ?string $state = null;

    public ?string $season = null;

    public ?int $year = null;

    public ?string $irrigationSource = null;

    // Quick Add Record Form Fields
    public string $farmerName = '';
    public ?string $formState = null;
    public ?int $formRegionId = null;
    public ?int $cropId = null;
    public string $formSeason = 'kharif';
    public float $area = 1.5;
    public string $irrigation = 'bore_well';
    public int $wellDepth = 150;
    public float $waterTable = 15.0;

    public ?string $formSuccess = null;

    /** @var array<string, mixed> */
    public array $kpis = [];

    /** @var array<int, array<string, mixed>> */
    public array $holdingDistribution = [];

    /** @var array<int, array<string, mixed>> */
    public array $irrigationBreakdown = [];

    /** @var array<int, array<string, mixed>> */
    public array $cropDistribution = [];

    /** @var array<int, array<string, mixed>> */
    public array $wellDepths = [];

    public function mount(AnalyticsService $analytics): void
    {
        $this->year = (int) date('Y');
        $this->cropId = \App\Models\Crop::first()?->id;
        $this->loadData($analytics);
    }

    public function updatedState(): void
    {
        $this->regionId = null;
    }

    public function updatedFormState(): void
    {
        $this->formRegionId = null;
    }

    public function updated($property, AnalyticsService $analytics): void
    {
        if (in_array($property, ['regionId', 'state', 'season', 'year', 'irrigationSource'], true)) {
            $this->loadData($analytics);
        }
    }

    public function pollData(AnalyticsService $analytics): void
    {
        $this->loadData($analytics);
    }

    public function refresh(AnalyticsService $analytics): void
    {
        $analytics->refreshAndBroadcast($this->filters());
        $this->loadData($analytics);
    }

    #[On('echo:analytics,analytics.updated')]
    public function onAnalyticsUpdated(array $data): void
    {
        $this->applyDashboardData($data);
        $this->dispatchChartUpdate();
    }

    /**
     * Save a quick agricultural record and instantly update stats
     */
    public function saveRecord(AnalyticsService $analytics): void
    {
        $this->validate([
            'farmerName' => 'required|string|max:255',
            'formRegionId' => 'required|exists:regions,id',
            'cropId' => 'required|exists:crops,id',
            'formSeason' => 'required|string|in:kharif,rabi,zaid',
            'area' => 'required|numeric|min:0.01',
            'irrigation' => 'required|string|in:canal,tube_well,dug_well,bore_well,rain_fed,river,tank_pond',
            'wellDepth' => 'required|integer|min:1',
            'waterTable' => 'required|numeric|min:0',
        ]);

        // 1. Create or retrieve farmer
        $farmer = \App\Models\Farmer::create([
            'name' => $this->farmerName,
            'farmer_code' => 'F-' . strtoupper(bin2hex(random_bytes(3))),
            'region_id' => $this->formRegionId,
            'enumerator_id' => auth()->id(),
        ]);

        // 2. Determine category
        $category = \App\Enums\LandHoldingCategory::Marginal;
        if ($this->area >= 10.0) {
            $category = \App\Enums\LandHoldingCategory::Large;
        } elseif ($this->area >= 4.0) {
            $category = \App\Enums\LandHoldingCategory::Medium;
        } elseif ($this->area >= 2.0) {
            $category = \App\Enums\LandHoldingCategory::SemiMedium;
        } elseif ($this->area >= 1.0) {
            $category = \App\Enums\LandHoldingCategory::Small;
        }

        // 3. Create LandHolding
        $holding = \App\Models\LandHolding::create([
            'farmer_id' => $farmer->id,
            'region_id' => $this->formRegionId,
            'survey_number' => 'MAN-' . strtoupper(bin2hex(random_bytes(2))),
            'area_hectares' => $this->area,
            'category' => $category,
            'is_irrigated' => $this->irrigation !== 'rain_fed',
            'latitude' => \App\Models\Region::find($this->formRegionId)?->latitude ?? 22.0,
            'longitude' => \App\Models\Region::find($this->formRegionId)?->longitude ?? 78.0,
        ]);

        // 4. Create CropPattern
        \App\Models\CropPattern::create([
            'region_id' => $this->formRegionId,
            'crop_id' => $this->cropId,
            'land_holding_id' => $holding->id,
            'season' => $this->formSeason,
            'year' => (int) date('Y'),
            'area_hectares' => $this->area,
            'yield_quintals' => $this->area * 25.0,
            'rotation_group' => 'A',
            'fertilizer_usage_kg' => $this->area * 120.0,
            'irrigation_dependent' => $this->irrigation !== 'rain_fed',
        ]);

        // 5. Create IrrigationRecord
        \App\Models\IrrigationRecord::create([
            'land_holding_id' => $holding->id,
            'region_id' => $this->formRegionId,
            'source_type' => $this->irrigation,
            'water_availability_score' => 4.0,
            'seasonal_usage' => 'Medium',
            'efficiency_percent' => 75.0,
            'water_stress' => false,
            'groundwater_level_m' => $this->waterTable,
        ]);

        // 6. Create Well
        \App\Models\Well::create([
            'region_id' => $this->formRegionId,
            'land_holding_id' => $holding->id,
            'well_type' => in_array($this->irrigation, ['bore_well', 'tube_well', 'dug_well']) ? $this->irrigation : 'bore_well',
            'depth_feet' => $this->wellDepth,
            'water_table_level_m' => $this->waterTable,
            'recharge_status' => 'moderate',
            'latitude' => $holding->latitude,
            'longitude' => $holding->longitude,
        ]);

        // 7. Sync land insights in real time
        try {
            resolve(\App\Services\LandInsightSyncService::class)->syncAll((int) date('Y'));
        } catch (\Exception $e) {
            // Ignore
        }

        // Reset form properties
        $this->farmerName = '';
        $this->area = 1.5;
        $this->wellDepth = 150;
        $this->waterTable = 15.0;
        
        $this->formSuccess = 'Agricultural record successfully created! Statistics and charts updated below.';

        // 8. Reload analytics data and dispatch event to charts
        $this->loadData($analytics);
    }

    /**
     * @return array<string, mixed>
     */
    public function chartPayload(): array
    {
        return [
            'holding' => $this->holdingDistribution,
            'irrigation' => $this->irrigationBreakdown,
            'crops' => $this->cropDistribution,
            'wells' => $this->wellDepths,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(): array
    {
        return array_filter([
            'region_id' => $this->regionId,
            'state' => $this->state,
            'season' => $this->season,
            'year' => $this->year,
            'irrigation_source' => $this->irrigationSource,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function loadData(AnalyticsService $analytics, bool $dispatchCharts = true): void
    {
        $this->applyDashboardData($analytics->dashboardData($this->filters()));

        if ($dispatchCharts) {
            $this->dispatchChartUpdate();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyDashboardData(array $data): void
    {
        $this->kpis = $data['kpis'] ?? [];
        $this->holdingDistribution = $data['holding_distribution'] ?? [];
        $this->irrigationBreakdown = $data['irrigation_breakdown'] ?? [];
        $this->cropDistribution = $data['crop_distribution'] ?? [];
        $this->wellDepths = $data['well_depths'] ?? [];
    }

    private function dispatchChartUpdate(): void
    {
        $payload = $this->chartPayload();
        $this->dispatch('charts-updated', payload: $payload);

        $this->js('window.agrolensLastChartPayload = '.json_encode($payload).'; window.AgroLensCharts?.render(window.agrolensLastChartPayload);');
    }

    public function render()
    {
        return view('livewire.analytics-dashboard', [
            'chartPayload' => $this->chartPayload(),
            'states' => Region::query()->distinct()->orderBy('state')->pluck('state'),
            'districts' => Region::query()
                ->districts()
                ->when($this->state, fn ($q) => $q->where('state', $this->state))
                ->orderBy('name')
                ->get(['id', 'name', 'state']),
            'formDistricts' => Region::query()
                ->districts()
                ->when($this->formState, fn ($q) => $q->where('state', $this->formState))
                ->orderBy('name')
                ->get(['id', 'name', 'state']),
            'crops' => \App\Models\Crop::orderBy('name')->get(),
            'seasons' => ['kharif', 'rabi', 'zaid'],
            'irrigationSources' => IrrigationSourceType::cases(),
        ]);
    }
}
