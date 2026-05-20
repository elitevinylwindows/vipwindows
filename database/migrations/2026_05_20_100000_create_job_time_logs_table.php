<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_time_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('user_id');
            $table->datetime('clock_in');
            $table->datetime('clock_out')->nullable();
            $table->integer('total_minutes')->nullable();
            $table->decimal('earnings', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('vip_jobs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('vip_users')->onDelete('cascade');
            $table->index(['user_id', 'clock_in']);
            $table->index(['job_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_time_logs');
    }
};
