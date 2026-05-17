<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_users', function (Blueprint $table) {
            $table->string('company_address')->nullable()->after('company_website');
            $table->string('company_city', 100)->nullable()->after('company_address');
            $table->string('company_state', 50)->nullable()->after('company_city');
            $table->string('company_zip', 20)->nullable()->after('company_state');
            $table->string('company_fax', 50)->nullable()->after('company_phone');
        });
    }

    public function down(): void
    {
        Schema::table('vip_users', function (Blueprint $table) {
            $table->dropColumn(['company_address', 'company_city', 'company_state', 'company_zip', 'company_fax']);
        });
    }
};
