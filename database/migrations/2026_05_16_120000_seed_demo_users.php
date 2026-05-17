<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Demo Installer
        if (!DB::table('vip_users')->where('email', 'installer@demo.vipwindows.net')->exists()) {
            DB::table('vip_users')->insert([
                'name'          => 'Demo Installer',
                'email'         => 'installer@demo.vipwindows.net',
                'password'      => Hash::make('demo1234'),
                'role'          => 'installer',
                'phone'         => '(555) 123-4567',
                'company_name'  => 'Demo Window Co.',
                'city'          => 'Los Angeles',
                'state'         => 'CA',
                'status'        => 'active',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Demo Customer
        if (!DB::table('vip_users')->where('email', 'customer@demo.vipwindows.net')->exists()) {
            DB::table('vip_users')->insert([
                'name'          => 'Demo Customer',
                'email'         => 'customer@demo.vipwindows.net',
                'password'      => Hash::make('demo1234'),
                'role'          => 'customer',
                'customer_type' => 'homeowner',
                'phone'         => '(555) 987-6543',
                'address'       => '123 Main St',
                'city'          => 'Beverly Hills',
                'state'         => 'CA',
                'zip'           => '90210',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('vip_users')->where('email', 'installer@demo.vipwindows.net')->delete();
        DB::table('vip_users')->where('email', 'customer@demo.vipwindows.net')->delete();
    }
};
