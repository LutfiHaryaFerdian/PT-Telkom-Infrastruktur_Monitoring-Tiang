<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KondisiTiang extends Model
{
    protected $table = 'kondisi_tiang';

    protected $fillable = ['nama', 'level'];

    public function tiangTelekomunikasi(): HasMany
    {
        return $this->hasMany(TiangTelekomunikasi::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Apakah kondisi ini termasuk NOK (Tidak OK)?
     * Level: perlu_perhatian atau rusak → NOK.
     */
    public function isNok(): bool
    {
        return in_array($this->level, ['perlu_perhatian', 'rusak']);
    }
}
