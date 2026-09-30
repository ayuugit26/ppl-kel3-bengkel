<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    protected $primaryKey = 'plat_nomor';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function antrean(): HasMany
    {
        return $this->hasMany(Antrean::class, 'plat_nomor', 'plat_nomor');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
