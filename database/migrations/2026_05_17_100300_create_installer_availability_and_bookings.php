<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Weekly recurring availability per installer
        Schema::create('installer_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installer_id');
            $table->tinyInteger('day_of_week'); // 0=Sunday, 1=Monday ... 6=Saturday
            $table->time('start_time');          // e.g. 08:00
            $table->time('end_time');            // e.g. 17:00
            $table->integer('slot_duration')->default(60);       // minutes per slot
            $table->integer('max_bookings_per_slot')->default(5); // bookings allowed per slot
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->foreign('installer_id')->references('id')->on('vip_users')->onDelete('cascade');
            $table->unique(['installer_id', 'day_of_week']);
        });

        // Date-specific overrides (days off, holidays, custom hours)
        Schema::create('installer_availability_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installer_id');
            $table->date('override_date');
            $table->boolean('is_available')->default(false); // false = day off
            $table->time('start_time')->nullable();          // custom start if available
            $table->time('end_time')->nullable();            // custom end if available
            $table->integer('max_bookings_per_slot')->nullable(); // override per-slot limit
            $table->string('reason', 255)->nullable();       // e.g. "Holiday", "Vacation"
            $table->timestamps();

            $table->foreign('installer_id')->references('id')->on('vip_users')->onDelete('cascade');
            $table->unique(['installer_id', 'override_date'], 'avail_override_unique');
        });

        // Customer bookings (no order required)
        Schema::create('installer_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 50)->unique();
            $table->unsignedBigInteger('installer_id');
            $table->unsignedBigInteger('customer_id')->nullable();

            // Customer info (filled even if guest)
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('install_address')->nullable();
            $table->string('install_city', 100)->nullable();
            $table->string('install_state', 50)->nullable();
            $table->string('install_zip', 20)->nullable();

            // Scheduling
            $table->date('booking_date');
            $table->time('booking_time');
            $table->string('service_type', 100)->nullable(); // e.g. "Window Installation", "Replacement"
            $table->text('description')->nullable();

            // Status
            $table->string('status', 30)->default('pending'); // pending, confirmed, completed, cancelled
            $table->text('notes')->nullable();
            $table->text('installer_notes')->nullable();

            // Link to quote if applicable
            $table->unsignedBigInteger('quote_id')->nullable();

            $table->timestamps();

            $table->foreign('installer_id')->references('id')->on('vip_users')->onDelete('cascade');
            $table->index(['installer_id', 'booking_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installer_bookings');
        Schema::dropIfExists('installer_availability_overrides');
        Schema::dropIfExists('installer_availability');
    }
};
