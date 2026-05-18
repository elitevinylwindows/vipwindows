<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_job_items', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('job_id');
            $table->decimal('unit_pay', 10, 2)->default(0)->after('qty')->comment('Installer pay per unit for this item');
            $table->decimal('total_pay', 10, 2)->default(0)->after('unit_pay')->comment('Calculated total pay (qty * unit_pay)');
        });
    }

    public function down(): void
    {
        Schema::table('vip_job_items', function (Blueprint $table) {
            $table->dropColumn(['service_id', 'unit_pay', 'total_pay']);
        });
    }
};
