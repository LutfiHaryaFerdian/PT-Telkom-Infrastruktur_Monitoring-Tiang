<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportHistory extends Model
{
    protected $table = 'import_histories';

    protected $fillable = [
        'filename',
        'uploaded_by',
        'total_rows',
        'success_rows',
        'failed_rows',
        'progress_percent',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportHistoryError::class, 'import_history_id');
    }
}
