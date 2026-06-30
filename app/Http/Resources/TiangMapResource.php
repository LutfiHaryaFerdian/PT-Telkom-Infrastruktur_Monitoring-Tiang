<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * [API] Response format ringkas untuk endpoint peta GIS.
 * Hanya menyertakan kolom yang dibutuhkan Leaflet marker — kolom besar (teks panjang) dihilangkan.
 * Digunakan di: GET /api/tiang/map
 */
class TiangMapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'kode_tiang'        => $this->kode_tiang,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'kondisi_level'     => $this->kondisi_level ?? $this->kondisiTiang?->level,
            'has_anomali'       => $this->has_anomali,
            'status_verifikasi' => $this->status_verifikasi,
        ];
    }
}
