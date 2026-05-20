<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalyticsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'kpis' => $this->resource['kpis'] ?? [],
            'holding_distribution' => $this->resource['holding_distribution'] ?? [],
            'irrigation_breakdown' => $this->resource['irrigation_breakdown'] ?? [],
            'crop_distribution' => $this->resource['crop_distribution'] ?? [],
            'well_depths' => $this->resource['well_depths'] ?? [],
            'map_markers' => $this->resource['map_markers'] ?? [],
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
