<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * [API] Response format lengkap untuk detail tiang.
 * Digunakan di: GET /api/tiang/{id} (popup Leaflet), GET tiang.show
 */
class TiangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                          => $this->id,
            'kode_tiang'                  => $this->kode_tiang,
            'id_tiang_instansi'           => $this->id_tiang_instansi,
            'nama_jalan'                  => $this->nama_jalan,
            'latitude'                    => $this->latitude,
            'longitude'                   => $this->longitude,
            'jml_tiang_operator_sekitar'  => $this->jml_tiang_operator_sekitar,
            'jml_kabel_dc_telkom'         => $this->jml_kabel_dc_telkom,
            'jml_ku_telkom'               => $this->jml_ku_telkom,
            'nama_teknisi'                => $this->nama_teknisi,
            'tgl_input'                   => $this->tgl_input?->toDateString(),
            'tanggal_temuan'              => $this->tanggal_temuan?->toDateString(),
            'status_verifikasi'           => $this->status_verifikasi,
            'has_anomali'                 => $this->has_anomali,
            'created_at'                  => $this->created_at?->toISOString(),
            'updated_at'                  => $this->updated_at?->toISOString(),

            // Relasi (hanya sertakan jika sudah di-load, hindari N+1)
            'sto'           => $this->whenLoaded('sto', fn () => [
                'id'   => $this->sto->id,
                'kode' => $this->sto->kode,
                'nama' => $this->sto->nama,
                'area' => $this->when(
                    $this->sto->relationLoaded('area'),
                    fn () => [
                        'id'       => $this->sto->area->id,
                        'name'     => $this->sto->area->name,
                        'district' => $this->when(
                            $this->sto->area->relationLoaded('district'),
                            fn () => [
                                'id'   => $this->sto->area->district->id,
                                'name' => $this->sto->area->district->name,
                            ]
                        ),
                    ]
                ),
            ]),
            'jenis_tiang'   => $this->whenLoaded('jenisTiang', fn () => [
                'id'      => $this->jenisTiang->id,
                'nama'    => $this->jenisTiang->nama,
                'tinggi_m'=> $this->jenisTiang->tinggi_m ?? null,
            ]),
            'kondisi_tiang' => $this->whenLoaded('kondisiTiang', fn () => [
                'id'    => $this->kondisiTiang->id,
                'nama'  => $this->kondisiTiang->nama,
                'level' => $this->kondisiTiang->level,
            ]),
            'foto_tiang'     => $this->whenLoaded('fotoTiang', fn () => $this->fotoTiang->map(fn ($f) => [
                'id'                => $f->id,
                'jenis_foto'        => $f->jenis_foto,
                'url'               => asset('storage/' . $f->path_file),
                'original_filename' => $f->original_filename,
            ])),
            'operators'      => $this->whenLoaded('tiangOperator', fn () => $this->tiangOperator->map(fn ($op) => [
                'id'               => $op->id,
                'operator_id'      => $op->operator_id,
                'nama_operator'    => $op->operator?->nama_operator,
                'jml_kabel_dc'     => $op->jml_kabel_dc,
                'jml_ku'           => $op->jml_ku,
                'jml_odp'          => $op->jml_odp,
                'status_legalitas' => $op->status_legalitas,
                'keterangan'       => $op->keterangan_operator,
            ])),
            'anomali_aktif'  => $this->whenLoaded('activeAnomalyLogs', fn () => AnomalyLogResource::collection($this->activeAnomalyLogs)),
        ];
    }
}
