<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $fillable = [
        'nama_barang',
        'stok',
        'harga',
    ];

    public function transaksiSpareparts()
    {
        return $this->hasMany(TransaksiSparepart::class);
    }
}
