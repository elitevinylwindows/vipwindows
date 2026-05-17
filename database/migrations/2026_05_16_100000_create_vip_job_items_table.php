<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_job_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->string('description', 255);           // e.g. "Double Hung Window 36x48"
            $table->string('item_type', 50)->nullable();   // window, door, service, other
            $table->decimal('qty', 8, 2)->default(1);
            $table->boolean('completed')->default(false);  // checklist-style completion
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('vip_jobs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_job_items');
    }
};
