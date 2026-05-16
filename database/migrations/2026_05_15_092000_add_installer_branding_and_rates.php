<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Installer branding fields
        Schema::table('vip_users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('status');
            $table->string('company_logo')->nullable()->after('company_name');
            $table->string('company_phone')->nullable()->after('company_logo');
            $table->string('company_email')->nullable()->after('company_phone');
            $table->string('company_website')->nullable()->after('company_email');
        });

        // Labor & service rates
        if (!Schema::hasTable('vip_service_rates')) {
            Schema::create('vip_service_rates', function (Blueprint $table) {
                $table->id();
                $table->string('category'); // 'labor', 'window', 'door', 'service'
                $table->string('name');
                $table->string('description')->nullable();
                $table->decimal('cost_rate', 10, 2)->default(0); // what we pay
                $table->decimal('charge_rate', 10, 2)->default(0); // what we charge
                $table->string('unit')->default('per_hour'); // per_hour, per_unit, flat
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('vip_users', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'company_logo', 'company_phone', 'company_email', 'company_website']);
        });
        Schema::dropIfExists('vip_service_rates');
    }
};
