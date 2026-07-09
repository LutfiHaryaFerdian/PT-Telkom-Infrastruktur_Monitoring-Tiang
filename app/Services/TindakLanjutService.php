<?php

namespace App\Services;

use App\Models\TiangOperator;
use App\Models\IspBalasan;
use Illuminate\Support\Facades\Cache;

class TindakLanjutService
{
    /**
     * Update status tindak lanjut untuk relasi tiang_operator.
     */
    public function updateStatus(TiangOperator $tiangOperator): void
    {
        // Invalidasi cache badge count di sidebar
        Cache::forget('tindaklanjut_perlu_followup_count');

        // Jika status ditandai selesai secara manual oleh admin, jangan di-override otomatis
        if ($tiangOperator->status_tindaklanjut === 'selesai') {
            return;
        }

        // 1. Hitung jumlah surat
        $suratCount = $tiangOperator->ispSurat()->count();

        // 2. Hitung jumlah balasan
        $suratIds = $tiangOperator->ispSurat()->pluck('id');
        $balasanCount = IspBalasan::whereIn('isp_surat_id', $suratIds)->count();

        // 3. Hitung hari sejak surat terakhir
        $suratTerakhir = $tiangOperator->ispSurat()->latest('tanggal_surat')->first();
        $hariSejak = null;
        if ($suratTerakhir && $suratTerakhir->tanggal_surat) {
            $hariSejak = now()->diffInDays($suratTerakhir->tanggal_surat);
        }

        // 4. Tentukan status
        if ($suratCount === 0) {
            $status = 'belum_disurati';
        } elseif ($balasanCount > 0) {
            $status = 'ada_balasan';
        } elseif ($hariSejak !== null && $hariSejak >= config('tindaklanjut.hari_followup', 14)) {
            $status = 'perlu_followup';
        } else {
            $status = 'sudah_disurati';
        }

        // 5. Update status
        $tiangOperator->update([
            'status_tindaklanjut' => $status,
            'tindaklanjut_updated_at' => now(),
        ]);
    }
}
