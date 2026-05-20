<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farmer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farmer_code', 'name', 'aadhaar_hash', 'phone', 'region_id',
        'ownership_type', 'household_size', 'latitude', 'longitude', 'enumerator_id',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function enumerator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enumerator_id');
    }

    public function landHoldings(): HasMany
    {
        return $this->hasMany(LandHolding::class);
    }
}
