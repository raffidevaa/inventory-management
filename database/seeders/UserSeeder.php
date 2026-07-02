<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $managerRole = Role::where('name', 'manager')->first();

        $users = [
            [
                'name' => 'Admin Inventaris',
                'email' => 'admin@telkom.com',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Gudang',
                'email' => 'staff@telkom.com',
                'password' => Hash::make('password123'),
                'role_id' => $staffRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manager Telkomsel',
                'email' => 'manager@telkom.com',
                'password' => Hash::make('password123'),
                'role_id' => $managerRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        User::insertOrIgnore($users);
    }
}
