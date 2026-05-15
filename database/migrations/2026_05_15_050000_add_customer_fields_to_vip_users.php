<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vip_users', function (Blueprint $table) {
            $table->string('address', 300)->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 50)->nullable()->after('city');
            $table->string('zip', 20)->nullable()->after('state');
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('vip_users', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'state', 'zip', 'notes']);
        });
    }
};
