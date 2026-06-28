<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@betini.local'],
            [
                'name' => 'Administrador Betini',
                'role' => 'admin',
                'password' => 'Betini@123',
                'email_verified_at' => now(),
            ],
        );
    }
}
