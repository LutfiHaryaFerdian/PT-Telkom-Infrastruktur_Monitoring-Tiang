<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TiangTelekomunikasi extends Model
{
    use SoftDeletes;

    protected $table = 'tiang_telekomunikasi';

    protected $fillable = [
        'kode_tiang',
        'id_tiang_instansi',
        'sto_id',
        'jenis_tiang_id',
        'kondisi_tiang_id',
        'latitude',
        'longitude',
        'nama_jalan',
        'jml_tiang_operator_sekitar',
        'jml_kabel_dc_telkom',
        'jml_ku_telkom',
        'nama_teknisi',
        'tgl_input',
        'tanggal_temuan',
        'status_verifikasi',
        // has_anomali TIDAK ada di fillable — hanya AnomalyDetectionService yang boleh ubah
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude'   => 'decimal:7',
        'longitude'  => 'decimal:7',
        'tgl_input'  => 'date',
        'tanggal_temuan' => 'date',
        'has_anomali' => 'boolean',
    ];

    // === RELASI ===

    public function sto(): BelongsTo
    {
        return $this->belongsTo(Sto::class);
    }

    public function jenisTiang(): BelongsTo
    {
        return $this->belongsTo(JenisTiang::class);
    }

    public function kondisiTiang(): BelongsTo
    {
        return $this->belongsTo(KondisiTiang::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function fotoTiang(): HasMany
    {
        return $this->hasMany(FotoTiang::class, 'tiang_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'tiang_id');
    }

    public function tiangOperator(): HasMany
    {
        return $this->hasMany(TiangOperator::class, 'tiang_id');
    }

    /**
     * Relasi BelongsToMany ke OperatorIsp (via pivot tiang_operator).
     */
    public function operatorIsps(): BelongsToMany
    {
        return $this->belongsToMany(OperatorIsp::class, 'tiang_operator', 'tiang_id', 'operator_id')
                    ->withPivot([
                        'id', 'jml_kabel_dc', 'jml_ku', 'jml_odp',
                        'keterangan_operator', 'status_legalitas',
                    ])
                    ->withTimestamps();
    }

    public function anomalyLogs(): HasMany
    {
        return $this->hasMany(AnomalyLog::class, 'tiang_id');
    }

    public function activeAnomalyLogs(): HasMany
    {
        return $this->hasMany(AnomalyLog::class, 'tiang_id')->where('status', 'aktif');
    }

    // === HELPERS ===

    /**
     * Apakah tiang ini memiliki semua 3 foto?
     */
    public function hasAllFotos(): bool
    {
        return $this->fotoTiang()->count() >= 3;
    }

    /**
     * Validasi transisi status verifikasi (state machine).
     * Mengembalikan true jika transisi valid.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return match ($this->status_verifikasi) {
            'pending'      => in_array($newStatus, ['ok', 'ditolak', 'double_input']),
            'ditolak'      => $newStatus === 'pending',
            'ok'           => false,
            'double_input' => false,
            default        => false,
        };
    }
}
