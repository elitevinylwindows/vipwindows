<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_services', function (Blueprint $table) {
            $table->decimal('installer_pay', 10, 2)->default(0)->after('cost_price');
            $table->string('installer_pay_type', 20)->default('per_unit')->after('installer_pay');
        });
    }

    public function down(): void
    {
        Schema::table('vip_services', function (Blueprint $table) {
            $table->dropColumn(['installer_pay', 'installer_pay_type']);
        });
    }
};
