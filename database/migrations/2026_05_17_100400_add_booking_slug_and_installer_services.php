<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add booking slug to installers for public booking links
        Schema::table('vip_users', function (Blueprint $table) {
            $table->string('booking_slug', 100)->nullable()->unique()->after('status');
        });

        // Installer-specific service pricing
        Schema::create('installer_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installer_id');
            $table->string('name');                          // e.g. "Window Installation"
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);     // base price
            $table->string('price_type', 30)->default('flat'); // flat, per_unit, per_hour, per_sqft
            $table->integer('estimated_duration')->nullable(); // minutes
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('installer_id')->references('id')->on('vip_users')->onDelete('cascade');
            $table->index('installer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installer_services');
        Schema::table('vip_users', function (Blueprint $table) {
            $table->dropColumn('booking_slug');
        });
    }
};
