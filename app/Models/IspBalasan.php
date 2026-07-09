<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IspBalasan extends Model
{
    protected $table = 'isp_balasan';

    protected $fillable = [
        'isp_surat_id',
        'tanggal_balasan',
        'isi_ringkasan',
        'file_balasan',
        'status_balasan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_balasan' => 'date',
    ];

    public function ispSurat(): BelongsTo
    {
        return $this->belongsTo(IspSurat::class, 'isp_surat_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
