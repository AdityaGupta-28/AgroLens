<?php

namespace App\Http\Controllers;

use App\Models\Crop;

class ToolsController extends Controller
{
    public function calculator()
    {
        $crops = Crop::query()
            ->orderBy('name')
            ->get(['id', 'name', 'scientific_name', 'type', 'season', 'optimal_ph_min', 'optimal_ph_max', 'water_requirement_days']);

        return view('tools.calculator', compact('crops'));
    }
}
