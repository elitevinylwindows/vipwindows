<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!Schema::hasColumn('messages', 'attachment')) {
                    $table->string('attachment')->nullable()->after('read_at');
                }
                if (!Schema::hasColumn('messages', 'attachment_name')) {
                    $table->string('attachment_name')->nullable()->after('attachment');
                }
                if (!Schema::hasColumn('messages', 'attachment_type')) {
                    $table->string('attachment_type')->nullable()->after('attachment_name');
                }
                if (!Schema::hasColumn('messages', 'attachment_size')) {
                    $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_type');
                }
                if (!Schema::hasColumn('messages', 'message_type')) {
                    $table->string('message_type')->default('text')->after('attachment_size');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $cols = ['message_type', 'attachment_size', 'attachment_type', 'attachment_name', 'attachment'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('messages', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
