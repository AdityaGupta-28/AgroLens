<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('land_insights', function (Blueprint $row) {
            $row->id();
            $row->foreignId('region_id')->constrained()->onDelete('cascade');
            $row->decimal('holding_size_avg', 8, 2); // in hectares
            $row->string('primary_irrigation_source'); // Tube Well, Canal, Rain-fed, etc.
            $row->string('cropping_pattern_type'); // Monoculture, Double-cropping, etc.
            $row->json('major_crops'); // Array of crops
            $row->integer('avg_well_depth'); // in feet
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('land_insights');
    }
};
