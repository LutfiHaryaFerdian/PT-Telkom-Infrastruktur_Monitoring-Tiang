<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisTiang extends Model
{
    protected $table = 'jenis_tiang';

    protected $fillable = ['nama', 'keterangan'];

    public function tiangTelekomunikasi(): HasMany
    {
        return $this->hasMany(TiangTelekomunikasi::class);
    }
}
