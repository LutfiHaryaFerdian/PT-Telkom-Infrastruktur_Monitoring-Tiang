<?php

namespace App\Observers;

use App\Models\TiangTelekomunikasi;
use Illuminate\Support\Facades\Cache;

/**
 * [PERFORMA + KEAMANAN] Observer untuk TiangTelekomunikasi.
 *
 * Tugasnya:
 *  1. Invalidasi cache dashboard secara otomatis saat ada perubahan data tiang.
 *     (Menghapus semua cache key yang dimulai dengan 'dashboard_stats_')
 *
 * Cara kerja invalidasi cache 'file' driver (tidak support tags):
 *  - Kita simpan versi/timestamp terakhir update di cache key khusus 'dashboard_version'.
 *  - DashboardApiController menyertakan versi ini dalam cache key-nya.
 *  - Saat versi berubah, cache key lama otomatis "expired" meskipun TTL belum habis.
 *
 * Catatan: Observer ini tidak duplikasi ActivityLog — pencatatan ActivityLog
 * tetap dilakukan manual di controller karena membutuhkan konteks request
 * (user yang sedang login, data before/after) yang tidak tersedia di Observer.
 */
class TiangObserver
{
    /**
     * Dijalankan setelah tiang baru dibuat.
     */
    public function created(TiangTelekomunikasi $tiang): void
    {
        $this->invalidateDashboardCache();
    }

    /**
     * Dijalankan setelah tiang diperbarui.
     */
    public function updated(TiangTelekomunikasi $tiang): void
    {
        $this->invalidateDashboardCache();
    }

    /**
     * Dijalankan setelah tiang dihapus (soft delete).
     */
    public function deleted(TiangTelekomunikasi $tiang): void
    {
        $this->invalidateDashboardCache();
    }

    /**
     * Dijalankan setelah tiang dipulihkan dari soft delete.
     */
    public function restored(TiangTelekomunikasi $tiang): void
    {
        $this->invalidateDashboardCache();
    }

    /**
     * Invalidasi semua cache dashboard dengan menaikkan versi.
     * DashboardApiController membaca versi ini dan menyertakannya di cache key.
     */
    private function invalidateDashboardCache(): void
    {
        // Increment versi (atau set timestamp baru) agar cache key lama tidak terpakai lagi.
        Cache::put('dashboard_version', now()->timestamp, 3600);
    }
}
