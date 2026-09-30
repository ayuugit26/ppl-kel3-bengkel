<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('user')->change();
        });

        DB::table('users')->where('role', 'kasir')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'user')->update(['role' => 'pelanggan']);

        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['admin', 'mekanik', 'pelanggan'])->default('pelanggan')->change();
        });

        DB::table('antreans')->where('status', 'selesai_pengerjaan')->update(['status' => 'Selesai']);
        DB::table('antreans')->where('status', 'lunas')->update(['status' => 'Lunas']);
        DB::table('antreans')->where('status', 'antre')->update(['status' => 'Antre']);
        DB::table('antreans')->where('status', 'proses')->update(['status' => 'Sedang Dikerjakan']);
        DB::table('antreans')->where('status', 'Selesai Pengerjaan')->update(['status' => 'Selesai']);
        DB::table('antreans')->where('status', 'Sudah Dibayar')->update(['status' => 'Lunas']);

        Schema::table('antreans', function (Blueprint $table): void {
            $table->enum('status', ['Antre', 'Sedang Dikerjakan', 'Selesai', 'Lunas'])->default('Antre')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('antreans', function (Blueprint $table): void {
            $table->string('status')->default('Antre')->change();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('pelanggan')->change();
        });
        DB::table('users')->where('role', 'pelanggan')->update(['role' => 'user']);
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['admin', 'kasir', 'mekanik', 'user'])->default('user')->change();
        });
    }
};
