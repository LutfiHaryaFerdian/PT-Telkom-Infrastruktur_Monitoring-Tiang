<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\District;
use App\Models\KondisiTiang;
use App\Models\Sto;
use App\Models\TiangTelekomunikasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function index(): View
    {
        $districts    = District::orderBy('name')->get();
        $kondisiTiang = KondisiTiang::orderBy('nama')->get();
        $stos         = Sto::whereNull('deleted_at')->orderBy('kode')->get();

        return view('export.index', compact('districts', 'kondisiTiang', 'stos'));
    }

    public function export(Request $request): Response
    {
        $request->validate([
            'format' => ['required', 'in:xlsx,pdf,csv'],
        ]);

        $filters = $request->only(['district_id', 'sto_id', 'kondisi', 'date_from', 'date_to']);
        $format  = $request->format;

        // Build query dengan filter
        $query = TiangTelekomunikasi::with(['sto.area.district', 'jenisTiang', 'kondisiTiang'])
            ->whereNull('deleted_at');

        if (! empty($filters['district_id'])) {
            $query->whereHas('sto.area', fn($q) => $q->where('district_id', $filters['district_id']));
        }
        if (! empty($filters['sto_id'])) {
            $query->where('sto_id', $filters['sto_id']);
        }
        if (! empty($filters['kondisi'])) {
            $query->whereHas('kondisiTiang', fn($q) => $q->where('level', $filters['kondisi']));
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('tgl_input', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('tgl_input', '<=', $filters['date_to']);
        }

        // Validasi PDF max 1000 baris
        if ($format === 'pdf') {
            $count = (clone $query)->count();
            if ($count > 1000) {
                return back()->with('error', "Data terlalu besar untuk PDF ({$count} baris). Gunakan format Excel.");
            }
        }

        $data = $query->get();

        $timestamp = now()->format('Ymd_His');
        $filename  = "tiang_export_{$timestamp}.{$format}";
        $userId    = auth()->id();

        // Catat activity log
        ActivityLog::record(
            'tiang', 0, 'created',
            "Sistem mengekspor data tiang format {$format} ({$data->count()} baris)",
            null, null, $userId
        );

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.tiang-pdf', ['tiang' => $data]);
            return $pdf->download($filename);
        }

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->streamDownload(function() use ($data) {
                $header = [
                    'Kode Tiang', 'ID Instansi', 'STO', 'Jenis Tiang', 'Kondisi',
                    'Latitude', 'Longitude', 'Nama Jalan',
                    'Jml Tiang Sekitar', 'Jml Kabel DC Telkom', 'Jml KU Telkom',
                    'Nama Teknisi', 'Tgl Input', 'Status Verifikasi', 'Has Anomali',
                ];

                $output = fopen('php://output', 'w');
                // UTF-8 BOM agar terbaca dengan benar di MS Excel
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($output, $header);

                foreach ($data as $t) {
                    fputcsv($output, [
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
                }
                fclose($output);
            }, $filename, $headers);
        }

        // Format xlsx
        $header = [
            'Kode Tiang', 'ID Instansi', 'STO', 'Jenis Tiang', 'Kondisi',
            'Latitude', 'Longitude', 'Nama Jalan',
            'Jml Tiang Sekitar', 'Jml Kabel DC Telkom', 'Jml KU Telkom',
            'Nama Teknisi', 'Tgl Input', 'Status Verifikasi', 'Has Anomali',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

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
        ])->toArray();

        $sheet->fromArray([$header, ...$rows]);

        $writer = new XlsxWriter($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
