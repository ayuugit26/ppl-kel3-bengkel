<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Memanggil BengkelSeeder agar data Sparepart, Jasa, & Karyawan ikut terisi
        $this->call([
            BengkelSeeder::class,
        ]);

        // ==================== USERS ====================

        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@bengkel.com'],
            [
                'name' => 'Admin Bengkel',
                'email' => 'admin@bengkel.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Kasir User
        User::updateOrCreate(
            ['email' => 'kasir@bengkel.com'],
            [
                'name' => 'Siti Kasir',
                'email' => 'kasir@bengkel.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        );
    }
}
