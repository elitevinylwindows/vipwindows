<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->string('to');
            $table->string('cc')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamps();

            $table->index('sent_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sent_emails');
    }
};
