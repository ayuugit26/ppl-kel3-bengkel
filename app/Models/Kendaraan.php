<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $primaryKey = 'plat_nomor';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function antrean()
    {
        return $this->hasMany(Antrean::class, 'plat_nomor', 'plat_nomor');
    }
}
