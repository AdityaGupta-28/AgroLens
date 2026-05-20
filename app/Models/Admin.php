<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    protected $table = 'admins';

    protected $fillable = [
        'user_id',
        'role',
        'is_active',
        'api_token',
        'api_token_hits',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_token_hits' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
