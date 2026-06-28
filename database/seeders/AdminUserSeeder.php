<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder {
    public function run(): void {
        // Asosiy admin (Ismoilxon)
        $admin = User::updateOrCreate(
            ['telegram_id' => 1738161732],
            [
                'name' => 'Ismoilxon Nurmatov',
                'email' => 'admin@cloudapi.uz',
                'password' => Hash::make('admin123'), // O'zgartiring!
                'telegram_username' => 'coder_nurmatov',
                'role' => 'admin',
                'status' => 'active',
                'country' => 'UZ',
                'language' => 'uz',
                'referral_code' => Str::upper(Str::random(8)),
                'coins' => 1000,
            ]
        );

        // Adminga wallet
        Wallet::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'balance_uzs' => 1000000, // 1M so'm test uchun
                'total_deposited' => 1000000,
            ]
        );

        $this->command->info('Admin user created: admin@cloudapi.uz / admin123');
        $this->command->warn('IMPORTANT: Change the admin password!');
    }
}