<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number', 50)->unique();
            $table->string('customer_number', 50)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('reference')->nullable();
            $table->date('entry_date')->nullable();
            $table->date('expected_delivery')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('entered_by')->nullable();
            $table->unsignedBigInteger('installer_id')->nullable();
            $table->string('order_type', 50)->nullable();
            $table->boolean('is_special_order')->default(false);
            $table->string('measurement_type', 20)->default('Imperial');

            // Tax
            $table->unsignedBigInteger('tax_rule_id')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);

            // Billing
            $table->string('billing_name')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 50)->nullable();
            $table->string('billing_zip', 20)->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone', 50)->nullable();

            // Shipping / Delivery
            $table->string('shipping_name')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_state', 50)->nullable();
            $table->string('shipping_zip', 20)->nullable();

            // Totals
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('installer_id');
            $table->index('entered_by');
            $table->index('status');
        });

        Schema::create('vip_quote_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quote_id');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('series_id')->nullable();
            $table->string('series_type', 50)->nullable();
            $table->decimal('width', 8, 3)->default(0);
            $table->decimal('height', 8, 3)->default(0);
            $table->string('glass', 100)->nullable();
            $table->string('grid', 100)->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->text('item_comment')->nullable();
            $table->text('internal_note')->nullable();

            // Color
            $table->string('color_config', 50)->nullable();
            $table->string('color_exterior', 100)->nullable();
            $table->string('color_exterior_custom', 100)->nullable();
            $table->string('color_interior', 100)->nullable();
            $table->string('color_interior_custom', 100)->nullable();

            // Frame / Glass / Options
            $table->string('frame_type', 100)->nullable();
            $table->string('fin_type', 100)->nullable();
            $table->string('glass_type', 100)->nullable();
            $table->string('spacer', 100)->nullable();
            $table->string('tempered', 100)->nullable();
            $table->string('specialty_glass', 100)->nullable();
            $table->json('tempered_fields')->nullable();
            $table->string('grid_pattern', 100)->nullable();
            $table->string('grid_profile', 100)->nullable();
            $table->string('grid_detail', 100)->nullable();

            // Booleans
            $table->boolean('retrofit_bottom_only')->default(false);
            $table->boolean('no_logo_lock')->default(false);
            $table->boolean('double_lock')->default(false);
            $table->boolean('custom_lock_position')->default(false);
            $table->boolean('custom_vent_latch')->default(false);
            $table->boolean('knocked_down')->default(false);
            $table->integer('checked_count')->default(0);
            $table->string('addon', 500)->nullable();

            // Shape
            $table->unsignedBigInteger('shape_definition_id')->nullable();
            $table->json('shape_params')->nullable();
            $table->string('shape_code', 50)->nullable();

            // Panel dimensions
            $table->json('panel_dimensions')->nullable();

            $table->timestamps();

            $table->foreign('quote_id')->references('id')->on('vip_quotes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_quote_items');
        Schema::dropIfExists('vip_quotes');
    }
};
