<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunAnomalyDetectionJob;
use App\Models\Area;
use App\Models\District;
use App\Models\ImportHistory;
use App\Models\ImportHistoryError;
use App\Models\JenisTiang;
use App\Models\KondisiTiang;
use App\Models\OperatorIsp;
use App\Models\Sto;
use App\Models\TiangTelekomunikasi;
use App\Models\TiangOperator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index(): View
    {
        $histories = ImportHistory::with('uploadedBy')
            ->latest()
            ->paginate(15);

        return view('import.index', compact('histories'));
    }

    public function show(ImportHistory $history): View
    {
        $history->load('uploadedBy');
        $errorLogs = $history->errors()->paginate(20);
        return view('import.show', compact('history', 'errorLogs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file'         => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
            'create_master'=> ['boolean'],
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();

        // Simpan file sementara
        $tmpPath = $file->store('imports/tmp', 'local');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tmpPath);

        $history = ImportHistory::create([
            'filename'    => $filename,
            'uploaded_by' => auth()->id(),
            'status'      => 'processing',
            'started_at'  => now(),
        ]);

        // Proses di background job
        \App\Jobs\ImportTiangJob::dispatch($history->id, $fullPath, $request->boolean('create_master'));

        return redirect()->route('import.show', $history)
            ->with('success', 'File berhasil diunggah dan sedang diproses. Halaman ini akan update otomatis.');
    }
}
