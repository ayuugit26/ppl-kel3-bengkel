<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiJasa extends Model
{
    protected $fillable = [
        'transaksi_id',
        'jasa_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function jasa()
    {
        return $this->belongsTo(Jasa::class);
    }
}
