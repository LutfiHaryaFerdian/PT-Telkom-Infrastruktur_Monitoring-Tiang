<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperatorIsp extends Model
{
    use SoftDeletes;

    protected $table = 'operator_isp';

    protected $fillable = ['nama_operator', 'is_predefined'];

    protected $casts = [
        'is_predefined' => 'boolean',
    ];

    public function tiangTelekomunikasi(): BelongsToMany
    {
        return $this->belongsToMany(TiangTelekomunikasi::class, 'tiang_operator', 'operator_id', 'tiang_id')
                    ->withPivot([
                        'id', 'jml_kabel_dc', 'jml_ku', 'jml_odp',
                        'keterangan_operator', 'status_legalitas',
                    ])
                    ->withTimestamps();
    }

    public function tiangOperator(): HasMany
    {
        return $this->hasMany(TiangOperator::class, 'operator_id');
    }

    /**
     * Cek apakah operator ini masih digunakan oleh tiang aktif.
     * Gunakan sebelum soft delete.
     */
    public function hasActiveTiang(): bool
    {
        return $this->tiangOperator()
                    ->whereHas('tiang', fn ($q) => $q->whereNull('deleted_at'))
                    ->exists();
    }
}
