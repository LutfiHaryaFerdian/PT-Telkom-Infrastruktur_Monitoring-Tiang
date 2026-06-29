<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoInspeksi extends Model
{
    protected $table = 'foto_inspeksi';

    protected $fillable = [
        'inspection_id',
        'jenis_foto',
        'path_file',
        'original_filename',
        'mime_type',
        'uploaded_by',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getPublicUrlAttribute(): string
    {
        return asset('storage/' . $this->path_file);
    }
}
