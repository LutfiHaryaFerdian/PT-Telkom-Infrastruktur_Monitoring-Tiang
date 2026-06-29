<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    public function tiang(): BelongsTo
    {
        return $this->belongsTo(TiangTelekomunikasi::class, 'tiang_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(OperatorIsp::class, 'operator_id');
    }
}
