<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\TiangTelekomunikasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportTiangJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly int $userId,
        public readonly string $format,   // 'xlsx', 'pdf', 'csv'
        public readonly array $filters,
        public readonly string $filename,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = "exports/{$this->userId}/{$this->filename}";

        // Build query dengan filter
        $query = TiangTelekomunikasi::with(['sto.area.district', 'jenisTiang', 'kondisiTiang'])
            ->whereNull('deleted_at');

        if (! empty($this->filters['district_id'])) {
            $query->whereHas('sto.area', fn($q) => $q->where('district_id', $this->filters['district_id']));
        }
        if (! empty($this->filters['sto_id'])) {
            $query->where('sto_id', $this->filters['sto_id']);
        }
        if (! empty($this->filters['kondisi'])) {
            $query->whereHas('kondisiTiang', fn($q) => $q->where('level', $this->filters['kondisi']));
        }
        if (! empty($this->filters['date_from'])) {
            $query->whereDate('tgl_input', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $query->whereDate('tgl_input', '<=', $this->filters['date_to']);
        }

        $data = $query->get();

        switch ($this->format) {
            case 'xlsx':
                $this->exportExcel($data, $path);
                break;

            case 'pdf':
                if ($data->count() > 1000) {
                    Log::error("ExportTiangJob: Data terlalu besar untuk PDF ({$data->count()} baris)");
                    return;
                }
                $this->exportPdf($data, $path);
                break;

            case 'csv':
                $this->exportCsv($data, $path);
                break;
        }

        Log::info("ExportTiangJob: selesai export {$this->format} → {$path}");

        // Catat activity log
        ActivityLog::record(
            'tiang', 0, 'created',
            "Sistem mengekspor data tiang format {$this->format} ({$data->count()} baris)",
            null, null, $this->userId
        );
    }

    protected function exportExcel($data, string $path): void
    {
        $rows = $data->map(fn($t) => [
            $t->kode_tiang,
            $t->id_tiang_instansi,
            $t->sto?->kode,
            $t->jenisTiang?->nama,
            $t->kondisiTiang?->nama,
            $t->latitude,
            $t->longitude,
            $t->nama_jalan,
            $t->jml_tiang_operator_sekitar,
            $t->jml_kabel_dc_telkom,
            $t->jml_ku_telkom,
            $t->nama_teknisi,
            $t->tgl_input?->format('Y-m-d'),
            $t->status_verifikasi,
            $t->has_anomali ? 'Ya' : 'Tidak',
        ]);

        $header = [
            'Kode Tiang', 'ID Instansi', 'STO', 'Jenis Tiang', 'Kondisi',
            'Latitude', 'Longitude', 'Nama Jalan',
            'Jml Tiang Sekitar', 'Jml Kabel DC Telkom', 'Jml KU Telkom',
            'Nama Teknisi', 'Tgl Input', 'Status Verifikasi', 'Has Anomali',
        ];

        // Simpan sebagai file sementara lalu pindah ke storage
        $tmpPath = sys_get_temp_dir() . '/' . basename($path);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header, ...$rows->toArray()]);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpPath);
        Storage::disk('public')->put($path, file_get_contents($tmpPath));
        unlink($tmpPath);
    }

    protected function exportPdf($data, string $path): void
    {
        $pdf = Pdf::loadView('exports.tiang-pdf', ['tiang' => $data]);
        $content = $pdf->output();
        Storage::disk('public')->put($path, $content);
    }

    protected function exportCsv($data, string $path): void
    {
        $header = [
            'Kode Tiang', 'ID Instansi', 'STO', 'Jenis Tiang', 'Kondisi',
            'Latitude', 'Longitude', 'Nama Jalan',
            'Jml Tiang Sekitar', 'Jml Kabel DC Telkom', 'Jml KU Telkom',
            'Nama Teknisi', 'Tgl Input', 'Status Verifikasi', 'Has Anomali',
        ];

        // UTF-8 BOM agar Excel tidak rusak karakter Indonesia
        $bom = "\xEF\xBB\xBF";
        $lines = [$bom . implode(',', array_map([$this, 'csvEscape'], $header))];

        foreach ($data as $t) {
            $lines[] = implode(',', array_map([$this, 'csvEscape'], [
                $t->kode_tiang,
                $t->id_tiang_instansi,
                $t->sto?->kode,
                $t->jenisTiang?->nama,
                $t->kondisiTiang?->nama,
                $t->latitude,
                $t->longitude,
                $t->nama_jalan,
                $t->jml_tiang_operator_sekitar,
                $t->jml_kabel_dc_telkom,
                $t->jml_ku_telkom,
                $t->nama_teknisi,
                $t->tgl_input?->format('Y-m-d'),
                $t->status_verifikasi,
                $t->has_anomali ? 'Ya' : 'Tidak',
            ]));
        }

        Storage::disk('public')->put($path, implode("\n", $lines));
    }

    protected function csvEscape($value): string
    {
        $value = (string)($value ?? '');
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ExportTiangJob gagal: {$exception->getMessage()}");
    }
}
