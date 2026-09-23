<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $guarded = [];

    public function antrean()
    {
        return $this->belongsTo(Antrean::class, 'antrean_id');
    }

    public function kasir()
    {
        return $this->belongsTo(Karyawan::class, 'id_kasir');
    }

    public function jasaDetails()
    {
        return $this->hasMany(TransaksiJasa::class);
    }

    public function sparepartDetails()
    {
        return $this->hasMany(TransaksiSparepart::class);
    }
}
