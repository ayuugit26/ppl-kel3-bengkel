<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    protected $guarded = [];

    public function antrean(): BelongsTo
    {
        return $this->belongsTo(Antrean::class, 'antrean_id');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_kasir');
    }

    public function jasaDetails(): HasMany
    {
        return $this->hasMany(TransaksiJasa::class);
    }

    public function sparepartDetails(): HasMany
    {
        return $this->hasMany(TransaksiSparepart::class);
    }
}
