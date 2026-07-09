<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IspSurat extends Model
{
    protected $table = 'isp_surat';

    protected $fillable = [
        'tiang_operator_id',
        'nomor_surat',
        'jenis_surat',
        'tanggal_surat',
        'perihal',
        'isi_ringkasan',
        'file_surat',
        'dikirim_oleh',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function tiangOperator(): BelongsTo
    {
        return $this->belongsTo(TiangOperator::class, 'tiang_operator_id');
    }

    public function dikirimOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function ispBalasan(): HasMany
    {
        return $this->hasMany(IspBalasan::class, 'isp_surat_id');
    }

    /**
     * Accessor: Cek apakah sudah ada balasan.
     */
    public function getStatusSuratAttribute(): string
    {
        return $this->ispBalasan()->exists() ? 'sudah_dibalas' : 'belum_dibalas';
    }
}
