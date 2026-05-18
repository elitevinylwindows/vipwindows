<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week');        // 0=Sun … 6=Sat
            $table->boolean('is_available')->default(true);
            $table->time('start_time')->default('08:00');
            $table->time('end_time')->default('17:00');
            $table->unsignedSmallInteger('slot_duration')->default(60);       // minutes
            $table->unsignedSmallInteger('max_bookings_per_slot')->default(5);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique('day_of_week');
        });

        Schema::create('admin_availability_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('override_date');
            $table->boolean('is_available')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedSmallInteger('max_bookings_per_slot')->nullable();
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique('override_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_availability_overrides');
        Schema::dropIfExists('admin_availability');
    }
};
