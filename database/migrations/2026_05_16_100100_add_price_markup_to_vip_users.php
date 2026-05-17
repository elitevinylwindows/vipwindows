<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_users', function (Blueprint $table) {
            $table->decimal('price_markup_pct', 5, 2)->default(0)->after('logo_path')
                  ->comment('Installer markup percentage added on top of admin price');
            $table->decimal('price_markup_flat', 10, 2)->default(0)->after('price_markup_pct')
                  ->comment('Flat dollar amount added per item on top of admin price');
        });
    }

    public function down(): void
    {
        Schema::table('vip_users', function (Blueprint $table) {
            $table->dropColumn(['price_markup_pct', 'price_markup_flat']);
        });
    }
};
