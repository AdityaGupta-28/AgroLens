<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnalyticsResource;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsApiController extends Controller
{
    public function dashboard(Request $request, AnalyticsService $analytics)
    {
        $data = $analytics->dashboardData($request->only([
            'region_id', 'state', 'season', 'year', 'irrigation_source',
        ]));

        return new AnalyticsResource($data);
    }
}
