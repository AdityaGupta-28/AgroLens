<?php

namespace App\Models;

use App\Enums\CropSeason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CropPattern extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'region_id', 'crop_id', 'land_holding_id', 'season', 'year',
        'area_hectares', 'yield_quintals', 'rotation_group',
        'fertilizer_usage_kg', 'irrigation_dependent',
    ];

    protected function casts(): array
    {
        return [
            'season' => CropSeason::class,
            'area_hectares' => 'decimal:4',
            'yield_quintals' => 'decimal:2',
            'fertilizer_usage_kg' => 'decimal:2',
            'irrigation_dependent' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function landHolding(): BelongsTo
    {
        return $this->belongsTo(LandHolding::class);
    }
}
