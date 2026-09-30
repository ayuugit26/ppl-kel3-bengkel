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
        DB::table('users')
            ->whereNotIn('role', ['admin', 'kasir', 'mekanik', 'user'])
            ->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['admin', 'kasir', 'mekanik', 'user'])->default('user')->change();
        });

        Schema::table('karyawans', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
        });

        Schema::table('kendaraans', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('antreans', function (Blueprint $table): void {
            $table->string('kode_antrean')->nullable()->unique();
            $table->string('status')->default('Antre')->change();
        });

        DB::table('antreans')->where('status', 'Selesai')->update(['status' => 'Selesai Pengerjaan']);
        DB::table('antreans')
            ->select('id')
            ->whereNull('kode_antrean')
            ->orderBy('id')
            ->chunkById(100, function ($antreans): void {
                foreach ($antreans as $antrean) {
                    DB::table('antreans')->where('id', $antrean->id)->update([
                        'kode_antrean' => 'SRV-'.str_pad((string) $antrean->id, 6, '0', STR_PAD_LEFT),
                    ]);
                }
            });

        $paidAntreanIds = DB::table('transaksis')->where('status_pembayaran', 'Lunas')->pluck('antrean_id');
        DB::table('antreans')->whereIn('id', $paidAntreanIds)->update(['status' => 'Sudah Dibayar']);

        Schema::table('transaksis', function (Blueprint $table): void {
            $table->enum('metode_pembayaran', ['Tunai', 'Non-Tunai'])->nullable()->after('uang_dibayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table): void {
            $table->dropColumn('metode_pembayaran');
        });

        Schema::table('antreans', function (Blueprint $table): void {
            $table->dropUnique('antreans_kode_antrean_unique');
            $table->dropColumn('kode_antrean');
        });
        DB::table('antreans')->whereIn('status', ['Selesai Pengerjaan', 'Sudah Dibayar'])->update(['status' => 'Selesai']);
        Schema::table('antreans', function (Blueprint $table): void {
            $table->enum('status', ['Antre', 'Sedang Dikerjakan', 'Selesai'])->default('Antre')->change();
        });

        Schema::table('kendaraans', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('karyawans', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('user')->change();
        });
    }
};
