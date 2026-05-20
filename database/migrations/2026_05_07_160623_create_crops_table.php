<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('scientific_name')->nullable();
            $table->string('type'); // Cereal, Pulse, Cash Crop, etc.
            $table->string('season'); // Kharif, Rabi, Zaid
            $table->decimal('optimal_ph_min', 4, 2);
            $table->decimal('optimal_ph_max', 4, 2);
            $table->integer('water_requirement_days');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
