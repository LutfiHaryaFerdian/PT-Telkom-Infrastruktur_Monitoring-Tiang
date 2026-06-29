<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AnomalyLog;
use App\Models\FotoInspeksi;
use App\Models\FotoTiang;
use App\Models\TiangTelekomunikasi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeTiangCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Hanya bisa dijalankan via CLI/tinker oleh admin.
     * --force: skip konfirmasi interaktif.
     */
    protected $signature = 'tiang:purge {id : ID tiang yang akan dihapus permanen} {--force : Skip konfirmasi}';

    protected $description = 'Hard delete tiang telekomunikasi + semua relasi dan file fisik (PERMANEN - tidak bisa dibatalkan)';

    public function handle(): int
    {
        $id = (int) $this->argument('id');

        // Ambil tiang (termasuk yang sudah soft-deleted)
        $tiang = TiangTelekomunikasi::withTrashed()->find($id);

        if (! $tiang) {
            $this->error("Tiang dengan ID {$id} tidak ditemukan.");
            return self::FAILURE;
        }

        $this->warn("⚠️  PERHATIAN: Aksi ini PERMANEN dan tidak bisa dibatalkan!");
        $this->line("Tiang: {$tiang->kode_tiang} (ID: {$tiang->id})");
        $this->line("Lokasi: {$tiang->nama_jalan}");

        if (! $this->option('force') && ! $this->confirm('Yakin ingin menghapus permanen tiang ini?')) {
            $this->info('Pembatalan purge.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($tiang, $id) {
            $this->line("\n🔍 Mengumpulkan data untuk dihapus...");

            // 1. Hapus file foto tiang
            $fotoTiang = FotoTiang::where('tiang_id', $id)->get();
            foreach ($fotoTiang as $foto) {
                if (Storage::disk('public')->exists($foto->path_file)) {
                    Storage::disk('public')->delete($foto->path_file);
                    $this->line("  🗑  Foto tiang dihapus: {$foto->path_file}");
                }
            }

            // Hapus direktori foto tiang jika kosong
            $dirFotoTiang = "foto_tiang/{$id}";
            if (Storage::disk('public')->exists($dirFotoTiang)) {
                $remainingFiles = Storage::disk('public')->files($dirFotoTiang);
                foreach ($remainingFiles as $orphanFile) {
                    Storage::disk('public')->delete($orphanFile);
                    $this->warn("  🗑  File orphan dihapus: {$orphanFile}");
                }
                Storage::disk('public')->deleteDirectory($dirFotoTiang);
            }

            // 2. Hapus file foto inspeksi
            $inspectionIds = $tiang->inspections()->pluck('id');
            $fotoInspeksi = FotoInspeksi::whereIn('inspection_id', $inspectionIds)->get();
            foreach ($fotoInspeksi as $foto) {
                if (Storage::disk('public')->exists($foto->path_file)) {
                    Storage::disk('public')->delete($foto->path_file);
                    $this->line("  🗑  Foto inspeksi dihapus: {$foto->path_file}");
                }
            }

            // 3. Hapus direktori foto inspeksi
            foreach ($inspectionIds as $inspId) {
                $dirInsp = "foto_inspeksi/{$inspId}";
                if (Storage::disk('public')->exists($dirInsp)) {
                    Storage::disk('public')->deleteDirectory($dirInsp);
                }
            }

            // 4. Catat ke activity_logs sebelum record dihapus
            ActivityLog::record(
                'tiang', $id, 'purged',
                "Sistem menghapus permanen (purge) tiang {$tiang->kode_tiang} (ID: {$id})",
                $tiang->toArray(), null, null
            );

            // 5. Hard delete tiang (cascade akan menghapus: tiang_operator, foto_tiang,
            //    inspections, foto_inspeksi, anomali_log, import terkait)
            $tiang->forceDelete();

            $this->info("✅ Tiang {$tiang->kode_tiang} berhasil dihapus permanen.");
        });

        return self::SUCCESS;
    }
}
