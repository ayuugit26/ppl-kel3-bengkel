<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Antrean extends Model
{
    public const STATUS_QUEUE = 'Antre';

    public const STATUS_WORKING = 'Sedang Dikerjakan';

    public const STATUS_FINISHED = 'Selesai';

    public const STATUS_PAID = 'Lunas';

    protected $guarded = [];

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'plat_nomor', 'plat_nomor');
    }

    public function mekanik(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_mekanik');
    }

    public function transaksi(): HasOne
    {
        return $this->hasOne(Transaksi::class, 'antrean_id');
    }
}
