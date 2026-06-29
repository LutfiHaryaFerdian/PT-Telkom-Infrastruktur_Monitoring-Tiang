<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoTiang extends Model
{
    protected $table = 'foto_tiang';

    protected $fillable = [
        'tiang_id',
        'jenis_foto',
        'path_file',
        'original_filename',
        'mime_type',
        'uploaded_by',
    ];

    public function tiang(): BelongsTo
    {
        return $this->belongsTo(TiangTelekomunikasi::class, 'tiang_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * URL publik untuk diakses di browser.
     */
    public function getPublicUrlAttribute(): string
    {
        return asset('storage/' . $this->path_file);
    }
}
