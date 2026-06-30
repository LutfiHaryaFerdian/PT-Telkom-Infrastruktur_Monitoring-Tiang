<?php

namespace App\Services;

use App\Models\TiangTelekomunikasi;
use App\Models\TiangOperator;
use App\Models\FotoTiang;
use App\Models\AnomalyLog;
use App\Models\Inspection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Sto;

class TiangService
{
    /**
     * Create tiang and its ISP relations inside a transaction.
     */
    public function createWithRelations(array $data): TiangTelekomunikasi
    {
        return DB::transaction(function () use ($data) {
            $sto = Sto::findOrFail($data['sto_id']);

            // Generate kode_tiang dengan locking parent STO row
            Sto::where('id', $sto->id)->lockForUpdate()->first();
            $last = TiangTelekomunikasi::where('sto_id', $sto->id)->max('kode_tiang');

            $lastNum = $last ? (int)substr($last, -5) : 0;
            $next    = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
            $kode    = "TI-{$sto->kode}-{$next}";

            $tiang = TiangTelekomunikasi::create(array_merge($data, [
                'kode_tiang'        => $kode,
                'created_by'        => auth()->id(),
                'status_verifikasi' => 'pending',
            ]));

            // Simpan operator ISP jika ada
            if (!empty($data['operators'])) {
                foreach ($data['operators'] as $op) {
                    TiangOperator::create([
                        'tiang_id'            => $tiang->id,
                        'operator_id'         => $op['operator_id'],
                        'jml_kabel_dc'        => $op['jml_kabel_dc'] ?? 0,
                        'jml_ku'              => $op['jml_ku'] ?? 0,
                        'jml_odp'             => $op['jml_odp'] ?? 0,
                        'keterangan_operator' => $op['keterangan'] ?? null,
                        'status_legalitas'    => $op['status_legalitas'] ?? 'perlu_verifikasi',
                    ]);
                }
            }

            return $tiang;
        }, 3);
    }

    /**
     * Update tiang and sync its ISP relations inside a transaction.
     */
    public function updateWithRelations(TiangTelekomunikasi $tiang, array $data): TiangTelekomunikasi
    {
        return DB::transaction(function () use ($tiang, $data) {
            $tiang->update(array_merge($data, [
                'updated_by' => auth()->id()
            ]));

            // Sync operator ISP jika ada di payload
            if (isset($data['operators'])) {
                $tiang->tiangOperator()->delete();
                foreach ($data['operators'] as $op) {
                    TiangOperator::create([
                        'tiang_id'            => $tiang->id,
                        'operator_id'         => $op['operator_id'],
                        'jml_kabel_dc'        => $op['jml_kabel_dc'] ?? 0,
                        'jml_ku'              => $op['jml_ku'] ?? 0,
                        'jml_odp'             => $op['jml_odp'] ?? 0,
                        'keterangan_operator' => $op['keterangan'] ?? null,
                        'status_legalitas'    => $op['status_legalitas'] ?? 'perlu_verifikasi',
                    ]);
                }
            }

            return $tiang;
        }, 3);
    }

    /**
     * Hard delete / purge tiang beserta all relations and physical files inside transaction.
     */
    public function purge(TiangTelekomunikasi $tiang): void
    {
        DB::transaction(function () use ($tiang) {
            // 1. Get foto paths to delete from storage
            $fotos = FotoTiang::where('tiang_id', $tiang->id)->get();
            
            // 2. Delete relations
            $tiang->tiangOperator()->delete();
            $tiang->anomalyLogs()->delete();
            
            // Delete inspections and inspection photos
            $inspections = Inspection::where('tiang_id', $tiang->id)->get();
            foreach ($inspections as $ins) {
                $ins->fotoInspeksi()->delete();
                $ins->delete();
            }

            $tiang->fotoTiang()->delete();

            // 3. Force delete the tiang itself
            $tiang->forceDelete();

            // 4. Delete files from storage disk
            foreach ($fotos as $f) {
                if ($f->path_file) {
                    Storage::disk('public')->delete($f->path_file);
                }
            }
            Storage::disk('public')->deleteDirectory("foto_tiang/{$tiang->id}");
        }, 3);
    }
}
