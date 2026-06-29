<?php

namespace App\Http\Controllers;

use App\Jobs\ExportTiangJob;
use App\Models\District;
use App\Models\KondisiTiang;
use App\Models\Sto;
use App\Models\TiangTelekomunikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExportController extends Controller
{
    public function index(): View
    {
        $districts    = District::orderBy('name')->get();
        $kondisiTiang = KondisiTiang::orderBy('nama')->get();
        $stos         = Sto::whereNull('deleted_at')->orderBy('kode')->get();

        return view('export.index', compact('districts', 'kondisiTiang', 'stos'));
    }

    public function export(Request $request): RedirectResponse
    {
        $request->validate([
            'format' => ['required', 'in:xlsx,pdf,csv'],
        ]);

        $filters = $request->only(['district_id', 'sto_id', 'kondisi', 'date_from', 'date_to']);
        $format  = $request->format;

        // Validasi PDF max 1000 baris
        if ($format === 'pdf') {
            $count = TiangTelekomunikasi::query()
                ->whereNull('deleted_at')
                ->when(! empty($filters['sto_id']), fn($q) => $q->where('sto_id', $filters['sto_id']))
                ->when(! empty($filters['district_id']), fn($q) => $q->whereHas('sto.area', fn($sq) => $sq->where('district_id', $filters['district_id'])))
                ->count();

            if ($count > 1000) {
                return back()->with('error', "Data terlalu besar untuk PDF ({$count} baris). Gunakan format Excel.");
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename  = "tiang_export_{$timestamp}.{$format}";
        $userId    = auth()->id();

        ExportTiangJob::dispatch($userId, $format, $filters, $filename);

        return back()->with('success', "Export {$format} sedang diproses. File akan tersedia di folder exports Anda.");
    }
}
