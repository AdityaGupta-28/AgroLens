<?php

namespace App\Models;

use App\Enums\IrrigationSourceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IrrigationRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'land_holding_id', 'region_id', 'source_type', 'water_availability_score',
        'seasonal_usage', 'efficiency_percent', 'water_stress', 'groundwater_level_m',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => IrrigationSourceType::class,
            'water_availability_score' => 'decimal:2',
            'efficiency_percent' => 'decimal:2',
            'water_stress' => 'boolean',
            'groundwater_level_m' => 'decimal:2',
        ];
    }

    public function landHolding(): BelongsTo
    {
        return $this->belongsTo(LandHolding::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
