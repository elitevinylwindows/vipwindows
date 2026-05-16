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

        // Seed default services
        DB::table('vip_services')->insertOrIgnore([
            ['name' => 'Door Install',    'code' => 'DOOR_INSTALL',    'description' => 'Full door installation service', 'base_price' => 350.00, 'cost_price' => 200.00, 'unit' => 'per_job', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Window Install',  'code' => 'WIN_INSTALL',     'description' => 'Full window installation service', 'base_price' => 250.00, 'cost_price' => 150.00, 'unit' => 'per_job', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Consultation',    'code' => 'CONSULTATION',    'description' => 'On-site or virtual consultation', 'base_price' => 75.00, 'cost_price' => 0.00, 'unit' => 'per_job', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Labor',           'code' => 'LABOR',           'description' => 'General labor charges', 'base_price' => 65.00, 'cost_price' => 35.00, 'unit' => 'per_hour', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_installer_services');
        Schema::dropIfExists('vip_services');
    }
};
