<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@jayapakan.test'],
            [
                'name' => 'Administrator Jaya Pakan',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'kasir@jayapakan.test'],
            [
                'name' => 'Kasir Jaya Pakan',
                'password' => Hash::make('Kasir123!'),
                'role' => 'kasir',
                'is_active' => true,
            ]
        );
    }
}
