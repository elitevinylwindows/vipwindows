<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add color to services for calendar color-coding
        Schema::table('vip_services', function (Blueprint $table) {
            $table->string('color', 7)->default('#0d6efd')->after('is_active'); // hex color
        });

        // Add duration/multi-day fields to installation orders
        Schema::table('installation_orders', function (Blueprint $table) {
            $table->string('duration_type', 20)->default('slot')->after('scheduled_slot'); // slot, full_day, multi_day
            $table->date('end_date')->nullable()->after('duration_type');                   // for multi-day jobs
            $table->decimal('estimated_hours', 5, 1)->nullable()->after('end_date');        // estimated hours for the job
        });
    }

    public function down(): void
    {
        Schema::table('vip_services', function (Blueprint $table) {
            $table->dropColumn('color');
        });
        Schema::table('installation_orders', function (Blueprint $table) {
            $table->dropColumn(['duration_type', 'end_date', 'estimated_hours']);
        });
    }
};
