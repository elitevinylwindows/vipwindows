<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('crew_id')->nullable()->after('assigned_to');
            $table->date('end_date')->nullable()->after('scheduled_date');
            $table->string('assignment_type', 10)->default('crew')->after('assigned_to')->comment('crew or installer');
        });
    }

    public function down(): void
    {
        Schema::table('vip_jobs', function (Blueprint $table) {
            $table->dropColumn(['crew_id', 'end_date', 'assignment_type']);
        });
    }
};
