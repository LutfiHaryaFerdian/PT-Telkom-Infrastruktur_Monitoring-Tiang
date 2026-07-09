<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IspFollowup extends Model
{
    protected $table = 'isp_followup';

    protected $fillable = [
        'tiang_operator_id',
        'tanggal_followup',
        'metode',
        'catatan',
        'hasil',
        'file_bukti',
        'dilakukan_oleh',
    ];

    protected $casts = [
        'tanggal_followup' => 'date',
    ];

    public function tiangOperator(): BelongsTo
    {
        return $this->belongsTo(TiangOperator::class, 'tiang_operator_id');
    }

    public function dilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}
