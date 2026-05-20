<?php

namespace App\Models;

use App\Enums\RegionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id', 'name', 'type', 'code', 'state',
        'population', 'agricultural_zone', 'latitude', 'longitude', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => RegionType::class,
            'metadata' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function landInsights(): HasMany
    {
        return $this->hasMany(LandInsight::class);
    }

    public function farmers(): HasMany
    {
        return $this->hasMany(Farmer::class);
    }

    public function landHoldings(): HasMany
    {
        return $this->hasMany(LandHolding::class);
    }

    public function wells(): HasMany
    {
        return $this->hasMany(Well::class);
    }

    public function cropPatterns(): HasMany
    {
        return $this->hasMany(CropPattern::class);
    }

    public function scopeDistricts($query)
    {
        return $query->where('type', RegionType::District);
    }

    public function scopeOfState($query, ?string $state)
    {
        if ($state) {
            $query->where('state', $state);
        }

        return $query;
    }
}
