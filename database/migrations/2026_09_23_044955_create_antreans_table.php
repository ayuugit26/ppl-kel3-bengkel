<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antreans', function (Blueprint $table) {
            $table->id();
            $table->string('plat_nomor');
            $table->foreign('plat_nomor')->references('plat_nomor')->on('kendaraans')->onDelete('cascade');
            $table->unsignedBigInteger('id_mekanik')->nullable();
            $table->foreign('id_mekanik')->references('id')->on('karyawans')->onDelete('set null');
            $table->text('keluhan');
            $table->enum('status', ['Antre', 'Sedang Dikerjakan', 'Selesai'])->default('Antre');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antreans');
    }
};
