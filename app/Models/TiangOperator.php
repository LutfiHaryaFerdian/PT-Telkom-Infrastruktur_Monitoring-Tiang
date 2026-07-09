<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TiangOperator extends Model
{
    protected $table = 'tiang_operator';

    protected $fillable = [
        'tiang_id',
        'operator_id',
        'jml_kabel_dc',
        'jml_ku',
        'jml_odp',
        'keterangan_operator',
        'status_legalitas',
        'status_tindaklanjut',
        'tindaklanjut_updated_at',
    ];

    protected $casts = [
        'tindaklanjut_updated_at' => 'datetime',
    ];

    public function tiang(): BelongsTo
    {
        return $this->belongsTo(TiangTelekomunikasi::class, 'tiang_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(OperatorIsp::class, 'operator_id');
    }

    public function ispSurat(): HasMany
    {
        return $this->hasMany(IspSurat::class, 'tiang_operator_id');
    }

    public function ispFollowup(): HasMany
    {
        return $this->hasMany(IspFollowup::class, 'tiang_operator_id');
    }

    /**
     * Accessor: return label Indonesia untuk status_tindaklanjut.
     */
    public function getStatusTindaklanjutLabelAttribute(): string
    {
        return match ($this->status_tindaklanjut) {
            'belum_disurati' => 'Belum Disurati',
            'sudah_disurati' => 'Sudah Disurati',
            'ada_balasan' => 'Ada Balasan',
            'perlu_followup' => 'Perlu Follow-up',
            'selesai' => 'Selesai',
            default => 'Belum Disurati',
        };
    }
}
