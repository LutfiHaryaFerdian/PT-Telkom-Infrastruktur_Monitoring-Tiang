<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    protected $table = 'inspections';

    protected $fillable = [
        'tiang_id',
        'inspected_by',
        'kondisi_tiang_id',
        'latitude',
        'longitude',
        'catatan',
        'inspected_at',
    ];

    protected $casts = [
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'inspected_at' => 'datetime',
    ];

    public function tiang(): BelongsTo
    {
        return $this->belongsTo(TiangTelekomunikasi::class, 'tiang_id');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function kondisiTiang(): BelongsTo
    {
        return $this->belongsTo(KondisiTiang::class);
    }

    public function fotoInspeksi(): HasMany
    {
        return $this->hasMany(FotoInspeksi::class, 'inspection_id');
    }

    /**
     * Apakah koordinat inspeksi berbeda dari koordinat tiang?
     */
    public function hasCoordinateDifference(): bool
    {
        if (! $this->latitude || ! $this->longitude) {
            return false;
        }

        $tiang = $this->tiang;
        return abs($this->latitude - $tiang->latitude) > 0.0001
            || abs($this->longitude - $tiang->longitude) > 0.0001;
    }
}
