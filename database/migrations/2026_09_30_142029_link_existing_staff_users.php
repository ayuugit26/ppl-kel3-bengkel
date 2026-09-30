<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ([
            ['admin@bengkel.com', 'Admin'],
            ['kasir@bengkel.com', 'Kasir'],
            ['mekanik@bengkel.com', 'Mekanik'],
        ] as [$email, $jabatan]) {
            $userId = DB::table('users')->where('email', $email)->value('id');
            if ($userId === null) {
                continue;
            }

            $karyawanId = DB::table('karyawans')
                ->where('jabatan', $jabatan)
                ->whereNull('user_id')
                ->orderBy('id')
                ->value('id');

            if ($karyawanId !== null) {
                DB::table('karyawans')->where('id', $karyawanId)->update(['user_id' => $userId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['admin@bengkel.com', 'kasir@bengkel.com', 'mekanik@bengkel.com'] as $email) {
            $userId = DB::table('users')->where('email', $email)->value('id');
            if ($userId !== null) {
                DB::table('karyawans')->where('user_id', $userId)->update(['user_id' => null]);
            }
        }
    }
};
