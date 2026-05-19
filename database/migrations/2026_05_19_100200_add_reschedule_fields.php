<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add reschedule tracking to vip_jobs
        if (Schema::hasTable('vip_jobs') && !Schema::hasColumn('vip_jobs', 'reschedule_reason')) {
            Schema::table('vip_jobs', function (Blueprint $table) {
                $table->text('reschedule_reason')->nullable()->after('notes');
                $table->timestamp('rescheduled_at')->nullable()->after('reschedule_reason');
                $table->date('rescheduled_from_date')->nullable()->after('rescheduled_at');
                $table->string('rescheduled_from_time')->nullable()->after('rescheduled_from_date');
            });
        }

        // Add reschedule tracking to calendar_events
        if (Schema::hasTable('calendar_events') && !Schema::hasColumn('calendar_events', 'reschedule_reason')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->text('reschedule_reason')->nullable()->after('customer_phone');
                $table->timestamp('rescheduled_at')->nullable()->after('reschedule_reason');
                $table->date('rescheduled_from_date')->nullable()->after('rescheduled_at');
                $table->string('rescheduled_from_time')->nullable()->after('rescheduled_from_date');
                $table->string('event_status')->default('scheduled')->after('rescheduled_from_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vip_jobs')) {
            Schema::table('vip_jobs', function (Blueprint $table) {
                $table->dropColumn(['reschedule_reason', 'rescheduled_at', 'rescheduled_from_date', 'rescheduled_from_time']);
            });
        }
        if (Schema::hasTable('calendar_events')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->dropColumn(['reschedule_reason', 'rescheduled_at', 'rescheduled_from_date', 'rescheduled_from_time', 'event_status']);
            });
        }
    }
};
