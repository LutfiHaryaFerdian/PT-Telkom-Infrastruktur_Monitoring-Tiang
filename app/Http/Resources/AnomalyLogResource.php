<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * [API] Response format untuk data anomali log.
 * Digunakan di: GET /api/anomali/aktif, resolved response
 */
class AnomalyLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tiang_id'     => $this->tiang_id,
            'kode_tiang'   => $this->tiang?->kode_tiang,
            'jenis'        => $this->jenis_anomali,
            'keterangan'   => $this->keterangan,
            'status'       => $this->status,
            'detected_at'  => $this->detected_at?->toISOString(),
            'resolved_at'  => $this->resolved_at?->toISOString(),
            'resolved_by'  => $this->resolved_by,
        ];
    }
}
