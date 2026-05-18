<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('vip_jobs', function (Blueprint $table) {
            $table->dropColumn('service_id');
        });
    }
};
