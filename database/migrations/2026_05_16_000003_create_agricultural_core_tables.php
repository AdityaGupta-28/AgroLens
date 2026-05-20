<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->string('farmer_code')->unique();
            $table->string('name');
            $table->string('aadhaar_hash')->nullable();
            $table->string('phone', 15)->nullable();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('ownership_type')->default('owner');
            $table->unsignedTinyInteger('household_size')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('enumerator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['region_id', 'name']);
        });

        Schema::create('land_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('survey_number')->nullable();
            $table->decimal('area_hectares', 10, 4);
            $table->string('category');
            $table->string('soil_type')->nullable();
            $table->string('land_category')->nullable();
            $table->boolean('is_irrigated')->default(false);
            $table->boolean('is_fragmented')->default(false);
            $table->unsignedTinyInteger('fragment_count')->default(1);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('document_path')->nullable();
            $table->string('tenant_details')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['region_id', 'category']);
        });

        Schema::create('irrigation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_holding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('source_type');
            $table->decimal('water_availability_score', 5, 2)->nullable();
            $table->string('seasonal_usage')->nullable();
            $table->decimal('efficiency_percent', 5, 2)->nullable();
            $table->boolean('water_stress')->default(false);
            $table->decimal('groundwater_level_m', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['region_id', 'source_type']);
        });

        Schema::create('wells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('land_holding_id')->nullable()->constrained()->nullOnDelete();
            $table->string('well_type');
            $table->unsignedInteger('depth_feet');
            $table->decimal('water_table_level_m', 8, 2)->nullable();
            $table->json('seasonal_variation')->nullable();
            $table->string('recharge_status')->nullable();
            $table->boolean('alert_low_groundwater')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['region_id', 'well_type']);
        });

        Schema::create('crop_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('land_holding_id')->nullable()->constrained()->nullOnDelete();
            $table->string('season');
            $table->unsignedSmallInteger('year');
            $table->decimal('area_hectares', 10, 4);
            $table->decimal('yield_quintals', 12, 2)->nullable();
            $table->string('rotation_group')->nullable();
            $table->decimal('fertilizer_usage_kg', 10, 2)->nullable();
            $table->boolean('irrigation_dependent')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['region_id', 'season', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_patterns');
        Schema::dropIfExists('wells');
        Schema::dropIfExists('irrigation_records');
        Schema::dropIfExists('land_holdings');
        Schema::dropIfExists('farmers');
    }
};
