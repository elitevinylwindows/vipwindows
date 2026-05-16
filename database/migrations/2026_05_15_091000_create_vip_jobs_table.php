<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('install_address')->nullable();
            $table->string('install_city')->nullable();
            $table->string('install_state')->nullable();
            $table->string('install_zip')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time')->nullable();
            $table->string('estimated_duration')->nullable();
            $table->datetime('actual_start')->nullable();
            $table->datetime('actual_end')->nullable();
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vip_job_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('vip_jobs')->onDelete('cascade');
            $table->text('note');
            $table->unsignedBigInteger('added_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_job_notes');
        Schema::dropIfExists('vip_jobs');
    }
};
