<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiSparepart extends Model
{
    protected $fillable = [
        'transaksi_id',
        'sparepart_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }
}
