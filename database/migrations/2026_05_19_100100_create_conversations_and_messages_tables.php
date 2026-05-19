<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id');
                $table->unsignedBigInteger('installer_id');
                $table->string('subject')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                $table->foreign('admin_id')->references('id')->on('vip_users')->onDelete('cascade');
                $table->foreign('installer_id')->references('id')->on('vip_users')->onDelete('cascade');
                $table->index(['admin_id', 'installer_id']);
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('sender_id');
                $table->text('body')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->string('attachment')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_type')->nullable();
                $table->unsignedBigInteger('attachment_size')->nullable();
                $table->string('message_type')->default('text'); // text, file, voice
                $table->timestamps();

                $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
                $table->foreign('sender_id')->references('id')->on('vip_users')->onDelete('cascade');
                $table->index('conversation_id');
                $table->index('sender_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
