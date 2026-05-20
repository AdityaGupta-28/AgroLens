<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('regions')->nullOnDelete();
            $table->string('type')->default('district')->after('name');
            $table->string('code', 20)->nullable()->after('type');
            $table->unsignedInteger('population')->nullable()->after('state');
            $table->string('agricultural_zone')->nullable()->after('population');
            $table->decimal('latitude', 10, 7)->nullable()->after('agricultural_zone');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->json('metadata')->nullable()->after('longitude');
            $table->softDeletes();
            $table->index(['type', 'state']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'parent_id', 'type', 'code', 'population',
                'agricultural_zone', 'latitude', 'longitude', 'metadata',
            ]);
        });
    }
};
