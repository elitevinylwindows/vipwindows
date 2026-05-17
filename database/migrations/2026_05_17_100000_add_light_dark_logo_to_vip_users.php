<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_users', function (Blueprint $table) {
            // Rename existing company_logo to company_logo_dark (for quotes/invoices on white backgrounds)
            $table->renameColumn('company_logo', 'company_logo_dark');
        });

        Schema::table('vip_users', function (Blueprint $table) {
            // Add light logo column (for sidebar/dark backgrounds)
            $table->string('company_logo_light')->nullable()->after('company_logo_dark');
        });
    }

    public function down(): void
    {
        Schema::table('vip_users', function (Blueprint $table) {
            $table->dropColumn('company_logo_light');
        });

        Schema::table('vip_users', function (Blueprint $table) {
            $table->renameColumn('company_logo_dark', 'company_logo');
        });
    }
};
