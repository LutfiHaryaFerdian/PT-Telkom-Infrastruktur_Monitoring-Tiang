<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sto extends Model
{
    use SoftDeletes;

    protected $table = 'stos';

    protected $fillable = ['area_id', 'kode', 'nama'];

    /**
     * Boot: kode selalu UPPERCASE sebelum disimpan.
     */
    protected static function booted(): void
    {
        static::saving(function (Sto $sto) {
            $sto->kode = strtoupper(trim($sto->kode));
        });
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function tiangTelekomunikasi(): HasMany
    {
        return $this->hasMany(TiangTelekomunikasi::class);
    }

    /**
     * Cek apakah STO masih digunakan oleh tiang aktif.
     * Gunakan sebelum soft delete untuk validasi.
     */
    public function hasActiveTiang(): bool
    {
        return $this->tiangTelekomunikasi()->whereNull('deleted_at')->exists();
    }

    /**
     * Hitung jumlah tiang aktif yang menggunakan STO ini.
     */
    public function countActiveTiang(): int
    {
        return $this->tiangTelekomunikasi()->whereNull('deleted_at')->count();
    }
}
