<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BengkelSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Karyawan (Mekanik & Kasir)
        DB::table('karyawans')->insert([
            ['nama_karyawan' => 'Budi Santoso', 'jabatan' => 'Admin', 'no_hp' => '08123456789'],
            ['nama_karyawan' => 'Agus Mekanik', 'jabatan' => 'Mekanik', 'no_hp' => '08234567890'],
            ['nama_karyawan' => 'Dedi Wijaya', 'jabatan' => 'Mekanik', 'no_hp' => '08345678901'],
            ['nama_karyawan' => 'Siti Kasir', 'jabatan' => 'Kasir', 'no_hp' => '08456789012'],
        ]);

        // 2. Data Sparepart
        DB::table('spareparts')->insert([
            ['nama_barang' => 'Oli Mesin 1L', 'stok' => 20, 'harga' => 55000],
            ['nama_barang' => 'Kampas Rem Depan', 'stok' => 15, 'harga' => 35000],
            ['nama_barang' => 'Busi Standard', 'stok' => 30, 'harga' => 20000],
            ['nama_barang' => 'Filter Udara', 'stok' => 10, 'harga' => 25000],
        ]);

        // 3. Data Jasa
        DB::table('jasas')->insert([
            ['nama_jasa' => 'Servis Rutin / Tune Up', 'harga' => 50000],
            ['nama_jasa' => 'Ganti Oli', 'harga' => 10000],
            ['nama_jasa' => 'Ganti Kampas Rem', 'harga' => 15000],
        ]);
    }
}
