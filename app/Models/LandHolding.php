<?php

namespace App\Models;

use App\Enums\LandHoldingCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandHolding extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farmer_id', 'region_id', 'survey_number', 'area_hectares', 'category',
        'soil_type', 'land_category', 'is_irrigated', 'is_fragmented',
        'fragment_count', 'latitude', 'longitude', 'document_path', 'tenant_details',
    ];

    protected function casts(): array
    {
        return [
            'area_hectares' => 'decimal:4',
            'category' => LandHoldingCategory::class,
            'is_irrigated' => 'boolean',
            'is_fragmented' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function irrigationRecords(): HasMany
    {
        return $this->hasMany(IrrigationRecord::class);
    }

    public function cropPatterns(): HasMany
    {
        return $this->hasMany(CropPattern::class);
    }
}
