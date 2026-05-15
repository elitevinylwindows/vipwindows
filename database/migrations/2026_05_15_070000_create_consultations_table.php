<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30)->nullable();
            $table->dateTime('scheduled_at');
            $table->integer('duration')->default(30); // minutes
            $table->string('platform', 20)->default('zoom'); // zoom, teams, phone
            $table->string('meeting_link', 500)->nullable();
            $table->text('notes')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('status', 20)->default('scheduled'); // scheduled, completed, cancelled, no_show
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultations');
    }
};
