<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('billing_address')->nullable();
            $table->enum('status', ['draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2);
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vip_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('vip_invoices')->onDelete('cascade');
            $table->string('description');
            $table->decimal('qty', 8, 2)->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_invoice_items');
        Schema::dropIfExists('vip_invoices');
    }
};
