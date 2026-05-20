<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalyticsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public array $data,
        public array $filters = []
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('analytics')];
    }

    public function broadcastAs(): string
    {
        return 'analytics.updated';
    }
}
