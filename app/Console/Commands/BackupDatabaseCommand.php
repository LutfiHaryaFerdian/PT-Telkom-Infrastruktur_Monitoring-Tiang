<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan backup database PostgreSQL dan menghapus file backup yang lebih dari 7 hari.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai backup database...');

        $connection = config('database.default');
        if ($connection !== 'pgsql') {
            $this->error('Backup command hanya mensupport database PostgreSQL saat ini.');
            return 1;
        }

        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        $database = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');

        // Folder penyimpanan backup
        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = "backup-{$database}-" . now()->format('Y-m-d_H-i-s') . ".sql";
        $outputPath = "{$backupDir}/{$filename}";

        // Siapkan command pg_dump
        // Set PGPASSWORD env variable jika password tidak kosong
        if (!empty($password)) {
            putenv("PGPASSWORD={$password}");
        }

        $command = sprintf(
            'pg_dump -U %s -h %s -p %s -F c -b -v -f %s %s 2>&1',
            escapeshellarg($username),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($outputPath),
            escapeshellarg($database)
        );

        $output = [];
        $returnVar = 0;
        
        exec($command, $output, $returnVar);

        // Reset PGPASSWORD env
        if (!empty($password)) {
            putenv("PGPASSWORD=");
        }

        if ($returnVar === 0) {
            $this->info("Backup database berhasil disimpan di: {$outputPath}");
            Log::info("Backup database PostgreSQL sukses: {$filename}");

            // Lakukan pembersihan file lama (Retention 7 hari)
            $this->cleanupOldBackups($backupDir);

            return 0;
        } else {
            $errorMessage = implode("\n", $output);
            $this->error("Gagal melakukan backup database. Error code: {$returnVar}");
            $this->error($errorMessage);
            Log::error("Backup database PostgreSQL gagal: {$errorMessage}");
            
            return 1;
        }
    }

    /**
     * Bersihkan file backup yang lebih tua dari 7 hari.
     */
    protected function cleanupOldBackups(string $backupDir): void
    {
        $this->info('Memeriksa file backup lama...');
        $files = glob("{$backupDir}/*.sql");
        $now = time();
        $retentionDays = 7;
        $retentionSeconds = $retentionDays * 24 * 60 * 60;

        foreach ($files as $file) {
            if (is_file($file)) {
                $fileAge = $now - filemtime($file);
                if ($fileAge > $retentionSeconds) {
                    unlink($file);
                    $filename = basename($file);
                    $this->line("Menghapus backup usang: {$filename}");
                    Log::info("Backup database usang dibersihkan: {$filename}");
                }
            }
        }
    }
}
