<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    protected $fillable = [
        'name',
        'scientific_name',
        'type',
        'season',
        'optimal_ph_min',
        'optimal_ph_max',
        'water_requirement_days',
    ];
}
