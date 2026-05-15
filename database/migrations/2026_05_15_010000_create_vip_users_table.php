<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vip_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->string('role', 20)->default('admin'); // admin, technician
            $table->string('password');
            $table->string('status', 20)->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        // Calendar availability slots set by VIP admin
        Schema::create('install_calendar_slots', function (Blueprint $table) {
            $table->id();
            $table->date('slot_date');
            $table->string('slot_time', 30); // e.g. "9:00 AM - 12:00 PM"
            $table->integer('max_bookings')->default(1);
            $table->integer('current_bookings')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['slot_date', 'slot_time']);
            $table->index('slot_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('install_calendar_slots');
        Schema::dropIfExists('vip_users');
    }
};
