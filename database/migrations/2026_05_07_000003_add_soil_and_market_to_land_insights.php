<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('land_insights', function (Blueprint $table) {
            $table->decimal('soil_ph', 4, 2)->nullable();
            $table->integer('nitrogen_level')->nullable(); // ppm
            $table->integer('phosphorus_level')->nullable(); // ppm
            $table->integer('potassium_level')->nullable(); // ppm
            $table->decimal('avg_rainfall', 8, 2)->nullable(); // mm
            $table->decimal('current_market_price', 10, 2)->nullable(); // INR per quintal
        });
    }

    public function down(): void
    {
        Schema::table('land_insights', function (Blueprint $table) {
            $table->dropColumn([
                'soil_ph',
                'nitrogen_level',
                'phosphorus_level',
                'potassium_level',
                'avg_rainfall',
                'current_market_price',
            ]);
        });
    }
};
