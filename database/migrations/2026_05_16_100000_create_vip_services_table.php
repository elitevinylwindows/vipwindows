<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vip_services')) {
            Schema::create('vip_services', function (Blueprint $table) {
                $table->id();
                $table->string('name');                         // e.g. "Window Install"
                $table->string('code', 30)->unique();           // e.g. "WIN_INSTALL"
                $table->text('description')->nullable();
                $table->decimal('base_price', 10, 2)->default(0);   // what we charge
                $table->decimal('cost_price', 10, 2)->default(0);   // what we pay
                $table->string('unit', 30)->default('per_job');     // per_job, per_hour, per_unit
                $table->decimal('min_price', 10, 2)->nullable();
                $table->decimal('max_price', 10, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Pivot: which installers offer which services
        if (!Schema::hasTable('vip_installer_services')) {
            Schema::create('vip_installer_services', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('installer_id');
                $table->unsignedBigInteger('service_id');
                $table->decimal('custom_price', 10, 2)->nullable(); // installer-specific override
                $table->timestamps();

                $table->unique(['installer_id', 'service_id']);
                $table->foreign('installer_id')->references('id')->on('vip_users')->onDelete('cascade');
                $table->foreign('service_id')->references('id')->on('vip_services')->onDelete('cascade');
            });
        }

        // No default services — admin defines their own
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_installer_services');
        Schema::dropIfExists('vip_services');
    }
};
