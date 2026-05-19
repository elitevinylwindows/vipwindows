<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vip_master_options')) {
            Schema::create('vip_master_options', function (Blueprint $table) {
                $table->id();
                $table->string('category', 50);   // unit, frame_type, grid, pattern
                $table->string('name', 150);
                $table->string('code', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('category');
                $table->index(['category', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_master_options');
    }
};
