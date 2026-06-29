<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyLog extends Model
{
    protected $table = 'anomali_log';

    protected $fillable = [
        'tiang_id',
        'jenis_anomali',
        'keterangan',
        'status',
        'detected_at',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function tiang(): BelongsTo
    {
        return $this->belongsTo(TiangTelekomunikasi::class, 'tiang_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope: hanya anomali aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
