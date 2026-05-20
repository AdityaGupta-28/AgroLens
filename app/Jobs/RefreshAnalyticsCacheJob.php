<?php

namespace App\Jobs;

use App\Services\AnalyticsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshAnalyticsCacheJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(public array $filters = []) {}

    public function handle(AnalyticsService $analytics): void
    {
        $analytics->refreshAndBroadcast($this->filters);
    }
}
