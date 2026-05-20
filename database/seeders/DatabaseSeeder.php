<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CropSeeder::class,
            AgroLensPlatformSeeder::class,
            LandInsightSeeder::class,
            SurveySeeder::class,
        ]);
    }
}
