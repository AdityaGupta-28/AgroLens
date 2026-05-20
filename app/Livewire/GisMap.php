<?php

namespace App\Livewire;

use App\Models\Region;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use Livewire\Component;

class GisMap extends Component
{
    public ?string $state = null;

    public ?int $regionId = null;

    public int $year;

    /** @var array<int, array<string, mixed>> */
    public array $markers = [];

    public function mount(AnalyticsRepositoryInterface $analytics): void
    {
        $this->year = (int) date('Y');
        $this->loadMarkers($analytics);
    }

    public function updatedState(AnalyticsRepositoryInterface $analytics): void
    {
        $this->regionId = null;
        $this->loadMarkers($analytics);
    }

    public function updatedRegionId(AnalyticsRepositoryInterface $analytics): void
    {
        $this->loadMarkers($analytics);
    }

    public function updatedYear(AnalyticsRepositoryInterface $analytics): void
    {
        $this->loadMarkers($analytics);
    }

    private function loadMarkers(AnalyticsRepositoryInterface $analytics): void
    {
        $this->markers = $analytics->getMapMarkers($this->state, $this->year, $this->regionId);
        $this->dispatch('map-markers-updated', markers: $this->markers);
    }

    public function render()
    {
        $currentYear = (int) date('Y');

        return view('livewire.gis-map', [
            'states' => Region::query()->districts()->distinct()->orderBy('state')->pluck('state'),
            'districts' => Region::query()
                ->districts()
                ->when($this->state, fn ($query) => $query->where('state', $this->state))
                ->orderBy('name')
                ->get(['id', 'name', 'state']),
            'years' => range($currentYear - 2, $currentYear),
        ]);
    }
}
