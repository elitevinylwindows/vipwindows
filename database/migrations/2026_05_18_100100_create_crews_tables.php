<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "Crew A", "North Team"
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::create('crew_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crew_id');
            $table->unsignedBigInteger('user_id');           // installer / technician
            $table->boolean('is_lead')->default(false);       // crew lead
            $table->timestamps();

            $table->foreign('crew_id')->references('id')->on('crews')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('vip_users')->onDelete('cascade');
            $table->unique(['crew_id', 'user_id']);
        });

        // Add crew_id to installation_orders so admin can assign a crew
        Schema::table('installation_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('crew_id')->nullable()->after('technician_id');
        });
    }

    public function down(): void
    {
        Schema::table('installation_orders', function (Blueprint $table) {
            $table->dropColumn('crew_id');
        });
        Schema::dropIfExists('crew_members');
        Schema::dropIfExists('crews');
    }
};
