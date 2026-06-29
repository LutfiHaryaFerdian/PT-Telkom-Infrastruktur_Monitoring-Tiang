<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportHistoryError extends Model
{
    protected $table = 'import_history_errors';

    protected $fillable = [
        'import_history_id',
        'row_number',
        'column_name',
        'error_message',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function importHistory(): BelongsTo
    {
        return $this->belongsTo(ImportHistory::class, 'import_history_id');
    }
}
