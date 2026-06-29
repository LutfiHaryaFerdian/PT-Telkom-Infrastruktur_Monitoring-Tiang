<?php

namespace App\Http\Controllers;

use App\Http\Requests\FotoInspeksiRequest;
use App\Models\ActivityLog;
use App\Models\FotoInspeksi;
use App\Models\Inspection;
use App\Models\TiangTelekomunikasi;
use App\Services\AnomalyDetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InspectionController extends Controller
{
    public function __construct(protected AnomalyDetectionService $anomalyService) {}

    /**
     * Tambah inspeksi baru untuk sebuah tiang.
     * Form ada di halaman show tiang.
     */
    public function store(Request $request, TiangTelekomunikasi $tiang): RedirectResponse
    {
        $request->validate([
            'kondisi_tiang_id' => ['required', 'exists:kondisi_tiang,id'],
            'latitude'         => ['nullable', 'numeric', 'between:-7.0,-4.0'],
            'longitude'        => ['nullable', 'numeric', 'between:104.0,107.0'],
            'catatan'          => ['nullable', 'string', 'max:2000'],
            'inspected_at'     => ['required', 'date'],
            'fotos'            => ['nullable', 'array'],
            'fotos.*'          => ['file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $inspection = DB::transaction(function () use ($request, $tiang) {
            $inspection = Inspection::create([
                'tiang_id'         => $tiang->id,
                'inspected_by'     => auth()->id(),
                'kondisi_tiang_id' => $request->kondisi_tiang_id,
                'latitude'         => $request->latitude,
                'longitude'        => $request->longitude,
                'catatan'          => $request->catatan,
                'inspected_at'     => $request->inspected_at,
            ]);

            // Upload foto inspeksi
            if ($request->hasFile('fotos')) {
                // Validasi layer 2: total foto tidak boleh > 10
                $existing = FotoInspeksi::where('inspection_id', $inspection->id)->count();
                $incoming = count($request->file('fotos'));

                if ($existing + $incoming > 10) {
                    throw new \RuntimeException('Maksimal 10 foto per inspeksi. Upload ' . $incoming . ' foto akan melebihi batas.');
                }

                foreach ($request->file('fotos') as $file) {
                    $uuid = Str::uuid();
                    $ext  = $file->getClientOriginalExtension();
                    $path = "foto_inspeksi/{$inspection->id}/{$uuid}.{$ext}";
                    $file->storeAs("foto_inspeksi/{$inspection->id}", "{$uuid}.{$ext}", 'public');

                    FotoInspeksi::create([
                        'inspection_id'     => $inspection->id,
                        'path_file'         => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type'         => $file->getMimeType(),
                        'uploaded_by'       => auth()->id(),
                    ]);
                }
            }

            return $inspection;
        });

        $user = auth()->user();
        ActivityLog::record(
            'inspection', $inspection->id, 'created',
            "{$user->getRoleDisplayName()} menambahkan inspeksi pada tiang {$tiang->kode_tiang}"
        );

        // Trigger anomaly detection
        $this->anomalyService->detect($tiang->fresh()->load(['kondisiTiang', 'tiangOperator.operator', 'fotoTiang']));

        return redirect()->route('tiang.show', $tiang)
            ->with('success', 'Inspeksi berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu inspeksi.
     */
    public function show(Inspection $inspection): View
    {
        $inspection->load(['tiang', 'inspectedBy', 'kondisiTiang', 'fotoInspeksi']);
        $hasDiff = $inspection->hasCoordinateDifference();

        return view('inspection.show', compact('inspection', 'hasDiff'));
    }

    /**
     * Terapkan koordinat inspeksi ke data tiang (OPSIONAL — bukan otomatis).
     */
    public function applyKoordinat(Inspection $inspection): RedirectResponse
    {
        if (! $inspection->latitude || ! $inspection->longitude) {
            return back()->with('error', 'Inspeksi ini tidak memiliki koordinat.');
        }

        $tiang = $inspection->tiang;
        $oldLat = $tiang->latitude;
        $oldLng = $tiang->longitude;

        $tiang->update([
            'latitude'   => $inspection->latitude,
            'longitude'  => $inspection->longitude,
            'updated_by' => auth()->id(),
        ]);

        $user = auth()->user();
        ActivityLog::record(
            'tiang', $tiang->id, 'koordinat_applied',
            "{$user->getRoleDisplayName()} menerapkan koordinat dari inspeksi #{$inspection->id} ke tiang {$tiang->kode_tiang}",
            ['latitude' => $oldLat, 'longitude' => $oldLng],
            ['latitude' => $inspection->latitude, 'longitude' => $inspection->longitude]
        );

        return back()->with('success', 'Koordinat tiang berhasil diperbarui dari data inspeksi.');
    }

    /**
     * Hapus inspeksi beserta foto-fotonya.
     */
    public function destroy(Inspection $inspection): RedirectResponse
    {
        $tiangId   = $inspection->tiang_id;
        $tiangKode = $inspection->tiang?->kode_tiang;

        DB::transaction(function () use ($inspection) {
            // Hapus file foto inspeksi
            foreach ($inspection->fotoInspeksi as $foto) {
                Storage::disk('public')->delete($foto->path_file);
            }
            // Cascade akan hapus foto_inspeksi records
            $inspection->delete();
        });

        $user = auth()->user();
        ActivityLog::record(
            'inspection', $inspection->id, 'deleted',
            "{$user->getRoleDisplayName()} menghapus inspeksi pada tiang {$tiangKode}"
        );

        return redirect()->route('tiang.show', $tiangId)
            ->with('success', 'Inspeksi berhasil dihapus.');
    }
}
