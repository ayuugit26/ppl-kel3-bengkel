<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jasa extends Model
{
    protected $fillable = [
        'nama_jasa',
        'harga',
    ];

    public function transaksiJasa()
    {
        return $this->hasMany(TransaksiJasa::class);
    }
}
