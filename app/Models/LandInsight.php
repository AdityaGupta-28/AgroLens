<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandInsight extends Model
{
    protected $fillable = [
        'region_id',
        'holding_size_avg',
        'primary_irrigation_source',
        'cropping_pattern_type',
        'major_crops',
        'avg_well_depth',
        'soil_ph',
        'nitrogen_level',
        'phosphorus_level',
        'potassium_level',
        'avg_rainfall',
        'current_market_price',
    ];

    protected $casts = [
        'major_crops' => 'array',
        'holding_size_avg' => 'float',
        'avg_well_depth' => 'integer',
        'soil_ph' => 'float',
        'nitrogen_level' => 'integer',
        'phosphorus_level' => 'integer',
        'potassium_level' => 'integer',
        'avg_rainfall' => 'float',
        'current_market_price' => 'float',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
