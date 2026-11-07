<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create super admin
        $admin = User::create([
            'name' => 'Barakah Admin',
            'email' => 'admin@barakah.test',
            'phone' => '01700000001',
            'password' => Hash::make('password'),
            'pin' => Hash::make('12345'),
            'role' => 'super_admin',
            'status' => 'active',
            'account_id' => 'BRK00000001',
        ]);

        // 2. Create admin's wallet with 1,000,000 balance
        Wallet::create([
            'user_id' => $admin->id,
            'balance' => 1000000.0000,
            'currency' => 'BDT',
            'status' => 'active',
        ]);

        // 3. Create an Agent user
        $agent = User::create([
            'name' => 'Barakah Agent 1',
            'email' => 'agent@barakah.test',
            'phone' => '01700000099',
            'password' => Hash::make('password'),
            'pin' => Hash::make('12345'),
            'role' => 'agent',
            'status' => 'active',
            'account_id' => 'BRK00000099',
        ]);

        // Wallet::create([
        //     'user_id' => $agent->id,
        //     'balance' => 500000.0000,
        //     'currency' => 'BDT',
        //     'status' => 'active',
        // ]);

        // 4. Create test users with wallets
        /*for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@barakah.test",
                'phone' => "0180000000{$i}",
                'password' => Hash::make('password'),
                'pin' => Hash::make('12345'),
                'role' => 'user',
                'status' => 'active',
                'account_id' => sprintf('BRK%08d', $i + 100),
            ]);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 10000.0000,
                'currency' => 'BDT',
                'status' => 'active',
            ]);
        }*/
    }
}
