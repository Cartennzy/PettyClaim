<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Default (Untuk Akses Utama Login Web Admin Keuangan)
        User::updateOrCreate(
            ['email' => 'admin@pettyclaim.com'],
            [
                'name' => 'Administrator Finance',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
    }
}