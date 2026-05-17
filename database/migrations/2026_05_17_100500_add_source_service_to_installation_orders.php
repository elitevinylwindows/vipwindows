<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installation_orders', function (Blueprint $table) {
            $table->string('source', 50)->default('admin')->after('status');
            $table->string('service_type', 100)->nullable()->after('source');
            $table->text('description')->nullable()->after('service_type');
        });
    }

    public function down(): void
    {
        Schema::table('installation_orders', function (Blueprint $table) {
            $table->dropColumn(['source', 'service_type', 'description']);
        });
    }
};
