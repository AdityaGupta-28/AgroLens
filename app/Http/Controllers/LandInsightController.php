<?php

namespace App\Http\Controllers;

use App\Enums\CropSeason;
use App\Models\CropPattern;
use App\Models\Farmer;
use App\Models\LandHolding;
use App\Models\LandInsight;
use App\Models\Well;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandInsightController extends Controller
{
    public function index(): View
    {
        $data = Cache::remember('land-insights.index.payload', 600, function () {
            $insights = LandInsight::query()
                ->with('region:id,name,state,agricultural_zone')
                ->join('regions', 'regions.id', '=', 'land_insights.region_id')
                ->orderBy('regions.state')
                ->orderBy('regions.name')
                ->select('land_insights.*')
                ->get();

            $regionIds = $insights->pluck('region_id')->unique()->all();

            $farmerCounts = Farmer::query()
                ->whereIn('region_id', $regionIds)
                ->groupBy('region_id')
                ->select('region_id', DB::raw('COUNT(*) as count'))
                ->pluck('count', 'region_id');

            $wellCounts = Well::query()
                ->whereIn('region_id', $regionIds)
                ->groupBy('region_id')
                ->select('region_id', DB::raw('COUNT(*) as count'))
                ->pluck('count', 'region_id');

            $stateSummaries = LandHolding::query()
                ->join('regions', 'regions.id', '=', 'land_holdings.region_id')
                ->whereIn('region_id', $regionIds)
                ->select(
                    'regions.state',
                    DB::raw('COUNT(DISTINCT land_holdings.region_id) as district_count'),
                    DB::raw('COUNT(land_holdings.id) as holdings_count'),
                    DB::raw('SUM(land_holdings.area_hectares) as total_area'),
                    DB::raw('SUM(CASE WHEN land_holdings.is_irrigated = 1 THEN land_holdings.area_hectares ELSE 0 END) as irrigated_area')
                )
                ->groupBy('regions.state')
                ->orderByDesc('total_area')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->state => [
                        'district_count' => (int) $row->district_count,
                        'holdings_count' => (int) $row->holdings_count,
                        'total_area' => (float) $row->total_area,
                        'irrigated_area' => (float) $row->irrigated_area,
                        'irrigation_pct' => $row->total_area > 0 ? round(($row->irrigated_area / $row->total_area) * 100, 1) : 0,
                    ],
                ]);

            $regionStats = LandHolding::query()
                ->whereIn('region_id', $regionIds)
                ->select(
                    'region_id',
                    DB::raw('COUNT(*) as holdings_count'),
                    DB::raw('SUM(area_hectares) as area_total'),
                    DB::raw('SUM(CASE WHEN is_irrigated = 1 THEN area_hectares ELSE 0 END) as irrigated_area')
                )
                ->groupBy('region_id')
                ->get()
                ->keyBy('region_id');

            $seasonSummary = CropPattern::query()
                ->whereIn('region_id', $regionIds)
                ->select('season', DB::raw('COUNT(*) as patterns'), DB::raw('SUM(area_hectares) as total_area'))
                ->groupBy('season')
                ->orderByDesc('total_area')
                ->get()
                ->map(fn ($row) => [
                    'season' => ucfirst($row->season instanceof CropSeason ? $row->season->value : (string) $row->season),
                    'pattern_count' => (int) $row->patterns,
                    'total_area' => (float) $row->total_area,
                ]);

            return compact('insights', 'regionStats', 'farmerCounts', 'wellCounts', 'stateSummaries', 'seasonSummary');
        });

        return view('land-insights.index', [
            ...$data,
            'dataSources' => config('agrolens.data_sources', []),
        ]);
    }
}
