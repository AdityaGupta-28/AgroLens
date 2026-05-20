<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Well extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'region_id', 'land_holding_id', 'well_type', 'depth_feet',
        'water_table_level_m', 'seasonal_variation', 'recharge_status',
        'alert_low_groundwater', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'seasonal_variation' => 'array',
            'alert_low_groundwater' => 'boolean',
            'water_table_level_m' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function landHolding(): BelongsTo
    {
        return $this->belongsTo(LandHolding::class);
    }
}
