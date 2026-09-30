<?php

namespace Database\Seeders;

use App\Models\Jasa;
use App\Models\Karyawan;
use App\Models\Sparepart;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Karyawan::doesntExist() && Sparepart::doesntExist() && Jasa::doesntExist()) {
            $this->call(BengkelSeeder::class);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@bengkel.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ],
        );

        $kasir = User::updateOrCreate(
            ['email' => 'kasir@bengkel.com'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ],
        );

        $mekanik = User::updateOrCreate(
            ['email' => 'mekanik@bengkel.com'],
            [
                'name' => 'Mekanik',
                'password' => Hash::make('password'),
                'role' => UserRole::Mechanic,
            ],
        );

        User::updateOrCreate(
            ['email' => 'jojo@bengkel.com'],
            [
                'name' => 'Jojo',
                'password' => Hash::make('password'),
                'role' => UserRole::Customer,
            ],
        );

        foreach ([
            [$admin, 'Admin', 'Budi Santoso'],
            [$kasir, 'Kasir', 'Siti Kasir'],
            [$mekanik, 'Mekanik', 'Agus Mekanik'],
        ] as [$user, $jabatan, $namaKaryawan]) {
            $karyawan = Karyawan::where('jabatan', $jabatan)
                ->where('nama_karyawan', $namaKaryawan)
                ->first();

            if ($karyawan !== null && ($karyawan->user_id === null || (int) $karyawan->user_id === $user->id)) {
                $karyawan->update(['user_id' => $user->id]);
            }
        }
    }
}
