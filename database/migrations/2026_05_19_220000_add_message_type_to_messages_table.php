<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('messages') && !Schema::hasColumn('messages', 'message_type')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('message_type')->default('text')->after('attachment_size');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('messages', 'message_type')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('message_type');
            });
        }
    }
};
