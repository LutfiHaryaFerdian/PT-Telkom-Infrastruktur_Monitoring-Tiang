<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    // Hanya created_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper statis untuk mencatat log dengan mudah.
     *
     * Format description standar:
     *   "{Role} {Nama} {aksi} {entitas} {identifier} [{detail opsional}]"
     */
    public static function record(
        string $modelType,
        int $modelId,
        string $action,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): self {
        $request = request();

        return static::create([
            'user_id'     => $userId ?? auth()->id(),
            'model_type'  => $modelType,
            'model_id'    => $modelId,
            'action'      => $action,
            'description' => $description,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => $request?->ip(),
            'user_agent'  => $request?->userAgent() ? substr($request->userAgent(), 0, 500) : null,
            'created_at'  => now(),
        ]);
    }
}
