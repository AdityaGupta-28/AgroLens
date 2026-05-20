<?php

namespace App\Services;

use App\Events\AnalyticsUpdated;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    public function __construct(
        private readonly RealtimeAnalyticsService $realtime
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboardData(array $filters = [], bool $fetchLiveApi = true): array
    {
        return $this->realtime->dashboardData($filters, $fetchLiveApi);
    }

    public function refreshAndBroadcast(array $filters = []): array
    {
        $regionId = $filters['region_id'] ?? null;
        $state = $filters['state'] ?? null;
        $season = $filters['season'] ?? null;
        $year = $filters['year'] ?? null;

        Cache::forget('analytics.kpis.'.($regionId ?? 'all').'.'.($state ?? 'all').'.'.($season ?? 'all').'.'.($year ?? 'all'));

        $data = $this->dashboardData($filters);
        AnalyticsUpdated::dispatch($data, $filters);

        return $data;
    }
}
