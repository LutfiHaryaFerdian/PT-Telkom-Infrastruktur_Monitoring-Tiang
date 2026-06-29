<?php

namespace App\Jobs;

use App\Models\ImportHistory;
use App\Services\AnomalyDetectionService;
use App\Models\TiangTelekomunikasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAnomalyDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum attempts before marking as failed.
     */
    public int $tries = 3;

    /**
     * Timeout in seconds.
     */
    public int $timeout = 600;

    /**
     * Backoff delay in seconds between retries.
     */
    public array $backoff = [60, 120, 300];

    public function __construct(
        public readonly int $importHistoryId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AnomalyDetectionService $anomalyService): void
    {
        $history = ImportHistory::findOrFail($this->importHistoryId);

        Log::info("RunAnomalyDetectionJob: mulai untuk import #{$this->importHistoryId}");

        // Proses batch tiang yang diimport
        // Ambil tiang yang dibuat selama import ini (berdasarkan created_at dalam range)
        $tiangQuery = TiangTelekomunikasi::whereNull('deleted_at')
            ->whereBetween('created_at', [
                $history->started_at,
                $history->finished_at ?? now(),
            ]);

        $total = $tiangQuery->count();
        $processed = 0;

        $tiangQuery->chunk(50, function ($tiangList) use ($anomalyService, &$processed, $total) {
            foreach ($tiangList as $tiang) {
                try {
                    $anomalyService->detect($tiang);
                } catch (\Exception $e) {
                    Log::warning("RunAnomalyDetectionJob: gagal detect tiang #{$tiang->id}: {$e->getMessage()}");
                }
                $processed++;
            }

            // Update progress setiap chunk
            if ($total > 0) {
                $progress = min(100, (int)(($processed / $total) * 100));
                Log::debug("RunAnomalyDetectionJob: progress anomali detection {$progress}%");
            }
        });

        Log::info("RunAnomalyDetectionJob: selesai untuk import #{$this->importHistoryId}, {$processed} tiang diproses.");
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("RunAnomalyDetectionJob gagal untuk import #{$this->importHistoryId}: {$exception->getMessage()}");

        ImportHistory::where('id', $this->importHistoryId)->update([
            'status' => 'failed',
        ]);
    }
}
