<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExportsCommand extends Command
{
    /**
     * File export TTL: 24 jam.
     * Jalankan via scheduler atau manual.
     */
    protected $signature = 'cleanup:exports {--dry-run : Tampilkan file yang akan dihapus tanpa benar-benar menghapus}';

    protected $description = 'Hapus file export yang lebih tua dari 24 jam dari storage/app/public/exports/';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $cutoff   = now()->subHours(24)->timestamp;
        $deleted  = 0;

        if (! Storage::disk('public')->exists('exports')) {
            $this->info('Direktori exports tidak ditemukan atau kosong.');
            return self::SUCCESS;
        }

        // Iterasi semua direktori user
        $userDirs = Storage::disk('public')->directories('exports');

        foreach ($userDirs as $userDir) {
            $files = Storage::disk('public')->files($userDir);

            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);

                if ($lastModified < $cutoff) {
                    if ($isDryRun) {
                        $this->line("  [DRY-RUN] Akan dihapus: {$file}");
                    } else {
                        Storage::disk('public')->delete($file);
                        $this->line("  🗑  Dihapus: {$file}");
                    }
                    $deleted++;
                }
            }

            // Hapus direktori user jika sudah kosong
            if (! $isDryRun) {
                $remaining = Storage::disk('public')->files($userDir);
                if (empty($remaining)) {
                    Storage::disk('public')->deleteDirectory($userDir);
                }
            }
        }

        $action = $isDryRun ? 'Akan dihapus' : 'Dihapus';
        $this->info("✅ {$action}: {$deleted} file export (> 24 jam).");

        return self::SUCCESS;
    }
}
