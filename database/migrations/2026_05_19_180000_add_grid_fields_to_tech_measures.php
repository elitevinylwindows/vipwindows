<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tech_measures') && !Schema::hasColumn('tech_measures', 'has_grids')) {
            Schema::table('tech_measures', function (Blueprint $table) {
                $table->string('frame_type', 100)->nullable()->after('notes');
                $table->boolean('has_grids')->default(false)->after('frame_type');
                $table->string('grid_list', 100)->nullable()->after('has_grids');
                $table->string('grid_pattern', 100)->nullable()->after('grid_list');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tech_measures')) {
            Schema::table('tech_measures', function (Blueprint $table) {
                $table->dropColumn(['has_grids', 'grid_list', 'grid_pattern']);
            });
        }
    }
};
