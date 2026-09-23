<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Antrean extends Model
{
    protected $guarded = [];

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'plat_nomor', 'plat_nomor');
    }

    public function mekanik()
    {
        return $this->belongsTo(Karyawan::class, 'id_mekanik');
    }

    public function transaksi()
    {
        return $this->hasOne(Transaksi::class, 'antrean_id');
    }
}
