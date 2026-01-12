<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@library.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        \App\Models\User::create([
            'name' => 'Staff User',
            'email' => 'staff@library.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);
    }
}
