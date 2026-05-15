<?php

namespace Database\Seeders;

use App\Models\VipUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        VipUser::updateOrCreate(
            ['email' => 'admin@vipwindows.net'],
            [
                'name'     => 'VIP Admin',
                'phone'    => null,
                'role'     => 'admin',
                'password' => Hash::make('VipAdmin2026!'),
                'status'   => 'active',
            ]
        );

        $this->command->info('Admin user created: admin@vipwindows.net / VipAdmin2026!');
    }
}
