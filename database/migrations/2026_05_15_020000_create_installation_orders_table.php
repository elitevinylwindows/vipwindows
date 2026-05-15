<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('installation_orders')) {
            Schema::create('installation_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quote_id')->nullable();
                $table->unsignedBigInteger('portal_user_id')->nullable();
                $table->string('customer_name');
                $table->string('customer_email')->nullable();
                $table->string('customer_phone', 30)->nullable();
                $table->string('install_address')->nullable();
                $table->string('install_address2')->nullable();
                $table->string('install_city')->nullable();
                $table->string('install_state', 50)->nullable();
                $table->string('install_zip', 20)->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 30)->default('pending'); // pending, scheduled, in_progress, completed, cancelled
                $table->date('scheduled_date')->nullable();
                $table->string('scheduled_slot', 50)->nullable();
                $table->unsignedBigInteger('technician_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('scheduled_date');
                $table->index('customer_email');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('installation_orders');
    }
};
