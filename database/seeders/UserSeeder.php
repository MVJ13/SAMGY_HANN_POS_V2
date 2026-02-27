<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin123'),
                'role'     => 'super_admin',
            ],
            [
                'name'     => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Cashier',
                'username' => 'cashier',
                'password' => Hash::make('cashier123'),
                'role'     => 'cashier',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
