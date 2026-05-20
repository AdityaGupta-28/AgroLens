<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyResponse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'survey_id', 'enumerator_id', 'region_id', 'farmer_id',
        'responses', 'status', 'latitude', 'longitude',
        'duplicate_hash', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'submitted_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function enumerator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enumerator_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }
}
