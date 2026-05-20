<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        if (Survey::exists()) {
            return;
        }

        $officer = User::query()->where('email', 'officer@agrolens.gov.in')->first();

        Survey::create([
            'title' => 'Kharif 2026 Crop Sowing Survey',
            'code' => 'KHARIF-2026',
            'description' => 'Field enumeration of sown area, seed variety, and irrigation source per holding.',
            'schema' => [
                'fields' => ['crop_id', 'area_hectares', 'season', 'irrigation_source', 'gps_coordinates'],
            ],
            'is_active' => true,
            'starts_at' => now()->startOfYear(),
            'ends_at' => now()->endOfYear(),
            'created_by' => $officer?->id,
        ]);

        Survey::create([
            'title' => 'Groundwater & Well Depth Assessment',
            'code' => 'GW-2026',
            'description' => 'Quarterly well depth, recharge status, and water stress indicators by district.',
            'schema' => [
                'fields' => ['well_type', 'depth_feet', 'water_table_level_m', 'recharge_status'],
            ],
            'is_active' => true,
            'starts_at' => now()->subMonths(3),
            'ends_at' => now()->addMonths(9),
            'created_by' => $officer?->id,
        ]);
    }
}
