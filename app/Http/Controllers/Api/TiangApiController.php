<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TiangMapResource;
use App\Http\Resources\TiangResource;
use App\Models\ActivityLog;
use App\Models\AnomalyLog;
use App\Models\FotoTiang;
use App\Models\Sto;
use App\Models\TiangTelekomunikasi;
use App\Services\AnomalyDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TiangApiController extends Controller
{
    public function __construct(protected AnomalyDetectionService $anomalyService) {}

    protected function success($data, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message], $code);
    }

    protected function error(string $message, int $code = 422): JsonResponse
    {
        return response()->json(['success' => false, 'data' => null, 'message' => $message], $code);
    }

    /**
     * GET /api/tiang/map
     * GIS endpoint — return data marker untuk Leaflet.
     */
    public function map(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 500), 1000);
        if ($request->filled('per_page') && (int) $request->get('per_page') > 1000) {
            return $this->error('Batas maksimum per_page adalah 1000', 422);
        }

        $query = TiangTelekomunikasi::query()
            ->select([
                'tiang_telekomunikasi.id',
                'tiang_telekomunikasi.kode_tiang',
                'tiang_telekomunikasi.latitude',
                'tiang_telekomunikasi.longitude',
                'kondisi_tiang.level as kondisi_level',
                'tiang_telekomunikasi.has_anomali',
                'tiang_telekomunikasi.status_verifikasi',
            ])
            ->join('kondisi_tiang', 'kondisi_tiang.id', '=', 'tiang_telekomunikasi.kondisi_tiang_id')
            ->whereNull('tiang_telekomunikasi.deleted_at');

        // Bbox filter (opsional)
        if ($request->filled('bbox')) {
            [$latMin, $lngMin, $latMax, $lngMax] = explode(',', $request->get('bbox'));
            $query->whereBetween('latitude', [(float)$latMin, (float)$latMax])
                  ->whereBetween('longitude', [(float)$lngMin, (float)$lngMax]);
        }

        // Filter tambahan
        if ($request->filled('district_id')) {
            $query->whereHas('sto.area', fn($q) => $q->where('district_id', $request->integer('district_id')));
        }
        if ($request->filled('sto_id')) {
            $query->where('sto_id', $request->integer('sto_id'));
        }
        if ($request->filled('kondisi')) {
            $query->where('kondisi_tiang.level', $request->get('kondisi'));
        }
        if ($request->filled('has_anomali')) {
            $query->where('has_anomali', filter_var($request->get('has_anomali'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('tgl_input', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl_input', '<=', $request->get('date_to'));
        }

        $page = max(1, (int) $request->get('page', 1));
        $data = $query->forPage($page, $perPage)->get();

        return $this->success([
            'page'     => $page,
            'per_page' => $perPage,
            'data'     => TiangMapResource::collection($data),
        ]);
    }

    /**
     * GET /api/tiang/map/bounds
     * Return bounding box area tiang berdasarkan filter.
     */
    public function bounds(Request $request): JsonResponse
    {
        $query = TiangTelekomunikasi::query()->whereNull('deleted_at');

        if ($request->filled('district_id')) {
            $query->whereHas('sto.area', fn($q) => $q->where('district_id', $request->integer('district_id')));
        }
        if ($request->filled('sto_id')) {
            $query->where('sto_id', $request->integer('sto_id'));
        }
        if ($request->filled('kondisi')) {
            $query->whereHas('kondisiTiang', fn($q) => $q->where('level', $request->get('kondisi')));
        }
        if ($request->filled('has_anomali')) {
            $query->where('has_anomali', filter_var($request->get('has_anomali'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('tgl_input', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl_input', '<=', $request->get('date_to'));
        }

        $bounds = $query->selectRaw('
            MIN(latitude) as lat_min, MIN(longitude) as lng_min,
            MAX(latitude) as lat_max, MAX(longitude) as lng_max
        ')->first();

        // Jika tidak ada data → default bounding box Lampung
        if (! $bounds->lat_min) {
            return $this->success([
                'lat_min' => -6.0,
                'lng_min' => 104.5,
                'lat_max' => -4.5,
                'lng_max' => 106.0,
            ]);
        }

        return $this->success($bounds);
    }

    /**
     * GET /api/tiang/{id}
     * Detail lengkap tiang untuk popup Leaflet.
     */
    public function show(int $id): JsonResponse
    {
        $tiang = TiangTelekomunikasi::with([
            'sto.area.district',
            'jenisTiang',
            'kondisiTiang',
            'fotoTiang',
            'tiangOperator.operator',
            'activeAnomalyLogs',
        ])->findOrFail($id);

        // [API Resource] Format response konsisten via TiangResource
        return $this->success(new TiangResource($tiang));
    }

    /**
     * GET /api/search/tiang?q={keyword}
     * Pencarian tiang via ILIKE (case-insensitive PostgreSQL).
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));
        if (empty($q)) {
            return $this->success([]);
        }

        $results = TiangTelekomunikasi::whereNull('deleted_at')
            ->where(function ($query) use ($q) {
                $query->whereRaw('kode_tiang ILIKE ?', ["%{$q}%"])
                      ->orWhereRaw('id_tiang_instansi ILIKE ?', ["%{$q}%"])
                      ->orWhereRaw('nama_jalan ILIKE ?', ["%{$q}%"]);
            })
            ->orderBy('kode_tiang')
            ->limit(10)
            ->get(['id', 'kode_tiang', 'id_tiang_instansi', 'nama_jalan', 'latitude', 'longitude']);

        return $this->success($results);
    }

    /**
     * PATCH /api/tiang/{id}/verifikasi
     * Validasi state machine verifikasi:
     *   pending → ok/ditolak/double_input ✓
     *   ditolak → pending ✓
     *   ok/double_input → lainnya ✗
     */
    public function verifikasi(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:ok,ditolak,double_input,pending'],
        ]);

        $tiang = TiangTelekomunikasi::whereNull('deleted_at')->findOrFail($id);
        $newStatus = $request->input('status');

        if (! $tiang->canTransitionTo($newStatus)) {
            return $this->error(
                "Transisi status dari '{$tiang->status_verifikasi}' ke '{$newStatus}' tidak diizinkan.",
                422
            );
        }

        $oldStatus = $tiang->status_verifikasi;
        $tiang->status_verifikasi = $newStatus;
        $tiang->save();

        $user = auth()->user();
        $aksi = $newStatus === 'ok' ? 'memverifikasi' : ($newStatus === 'ditolak' ? 'menolak' : 'mengubah status');
        ActivityLog::record(
            'tiang', $tiang->id,
            $newStatus === 'ok' ? 'verified' : 'rejected',
            "{$user->getRoleDisplayName()} {$aksi} tiang {$tiang->kode_tiang}",
            ['status_verifikasi' => $oldStatus],
            ['status_verifikasi' => $newStatus],
        );

        return $this->success($tiang->fresh(), 'Status verifikasi berhasil diperbarui.');
    }

    /**
     * PATCH /api/tiang/{id}/kode
     * Admin only — update kode_tiang manual.
     */
    public function updateKode(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'kode_tiang' => ['required', 'string', 'max:30', "unique:tiang_telekomunikasi,kode_tiang,{$id}"],
        ]);

        $tiang = TiangTelekomunikasi::whereNull('deleted_at')->findOrFail($id);
        $oldKode = $tiang->kode_tiang;
        $tiang->kode_tiang = $request->input('kode_tiang');
        $tiang->save();

        $user = auth()->user();
        ActivityLog::record(
            'tiang', $tiang->id, 'kode_updated',
            "{$user->getRoleDisplayName()} mengubah kode tiang dari {$oldKode} menjadi {$tiang->kode_tiang}",
            ['kode_tiang' => $oldKode],
            ['kode_tiang' => $tiang->kode_tiang],
        );

        return $this->success($tiang->fresh(), 'Kode tiang berhasil diperbarui.');
    }

    /**
     * PATCH /api/tiang/{id}/isp/{operator_id}/legalitas
     * Admin only — update status legalitas ISP.
     */
    public function updateLegalitas(Request $request, int $id, int $operatorId): JsonResponse
    {
        $request->validate([
            'status_legalitas' => ['required', 'in:legal,ilegal,perlu_verifikasi'],
        ]);

        $tiang = TiangTelekomunikasi::whereNull('deleted_at')->findOrFail($id);
        $pivot = $tiang->tiangOperator()->where('operator_id', $operatorId)->firstOrFail();
        $oldStatus = $pivot->status_legalitas;
        $newStatus = $request->input('status_legalitas');

        $pivot->status_legalitas = $newStatus;
        $pivot->save();

        $user = auth()->user();
        $namaOperator = $pivot->operator?->nama_operator ?? "Operator #{$operatorId}";
        $labelBaru = ucfirst(str_replace('_', ' ', $newStatus));

        ActivityLog::record(
            'tiang_operator', $pivot->id, 'legalitas_updated',
            "{$user->getRoleDisplayName()} mengubah legalitas {$namaOperator} di {$tiang->kode_tiang} menjadi {$labelBaru}",
            ['status_legalitas' => $oldStatus],
            ['status_legalitas' => $newStatus],
        );

        // Trigger anomaly detection
        $this->anomalyService->detect($tiang->fresh());

        return $this->success($pivot->fresh(), 'Status legalitas berhasil diperbarui.');
    }

    /**
     * POST /api/tiang/{id}/foto
     * Upload foto tiang (depan/kanan/kiri).
     * [KEAMANAN] Validasi MIME asli via finfo, nama file disimpan sebagai UUID (bukan nama user).
     */
    public function uploadFoto(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'jenis_foto' => ['required', 'in:depan,kanan,kiri'],
            'foto'       => ['required', 'file', 'max:5120'], // maks 5MB
        ]);

        $tiang = TiangTelekomunikasi::whereNull('deleted_at')->findOrFail($id);
        $jenis = $request->input('jenis_foto');
        $file  = $request->file('foto');

        // [KEAMANAN] Validasi MIME asli via finfo — bukan hanya ekstensi nama file
        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($realMime, $allowedMimes)) {
            return $this->error('Tipe file tidak diizinkan. Hanya JPEG, PNG, atau WebP yang diterima.', 422);
        }

        // [KEAMANAN] Nama file fisik di storage SELALU UUID — bukan nama asli dari user
        $ext          = $file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $uuidFilename = Str::uuid() . '.' . strtolower($ext);
        $storagePath  = "foto_tiang/{$id}/{$uuidFilename}";

        DB::transaction(function () use ($tiang, $jenis, $file, $id, $storagePath, $uuidFilename, $realMime) {
            // Hapus foto lama jika ada
            $existing = FotoTiang::where('tiang_id', $id)->where('jenis_foto', $jenis)->first();
            if ($existing) {
                Storage::disk('public')->delete($existing->path_file);
                $existing->delete();
            }

            // Simpan file dengan nama UUID
            $file->storeAs("foto_tiang/{$id}", $uuidFilename, 'public');

            FotoTiang::create([
                'tiang_id'          => $id,
                'jenis_foto'        => $jenis,
                'path_file'         => $storagePath,
                'original_filename' => $file->getClientOriginalName(), // nama asli disimpan di DB saja
                'mime_type'         => $realMime,
                'uploaded_by'       => auth()->id(),
            ]);
        });

        // Trigger anomaly detection (data_tidak_lengkap tergantung foto)
        $this->anomalyService->detect($tiang->fresh()->load('fotoTiang'));

        return $this->success(null, 'Foto tiang berhasil diupload.');
    }

    /**
     * GET /api/tiang/heatmap
     */
    public function heatmap(Request $request): JsonResponse
    {
        $type = $request->get('type');
        if (!in_array($type, ['tiang', 'anomali'])) {
            return $this->error('Parameter type wajib diisi dengan nilai "tiang" atau "anomali"', 422);
        }

        $query = TiangTelekomunikasi::query()
            ->whereNull('tiang_telekomunikasi.deleted_at');

        // Apply filters
        if ($request->filled('district_id')) {
            $query->whereHas('sto.area', fn($q) => $q->where('district_id', $request->integer('district_id')));
        }
        if ($request->filled('area_id')) {
            $query->whereHas('sto', fn($q) => $q->where('area_id', $request->integer('area_id')));
        }
        if ($request->filled('sto_id')) {
            $query->where('sto_id', $request->integer('sto_id'));
        }
        if ($request->filled('has_anomali')) {
            $query->where('has_anomali', filter_var($request->get('has_anomali'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('tgl_input', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl_input', '<=', $request->get('date_to'));
        }

        if ($type === 'anomali') {
            $query->join('anomali_log', function($join) {
                $join->on('anomali_log.tiang_id', '=', 'tiang_telekomunikasi.id')
                     ->where('anomali_log.status', '=', 'aktif');
            });
            
            $data = $query->selectRaw('ROUND(tiang_telekomunikasi.latitude::numeric, 3) as latitude, ROUND(tiang_telekomunikasi.longitude::numeric, 3) as longitude, COUNT(anomali_log.id) as weight')
                ->groupBy(DB::raw('ROUND(tiang_telekomunikasi.latitude::numeric, 3), ROUND(tiang_telekomunikasi.longitude::numeric, 3)'))
                ->get()
                ->map(fn($item) => [
                    'latitude' => (float)$item->latitude,
                    'longitude' => (float)$item->longitude,
                    'weight' => (int)$item->weight,
                ])
                ->toArray();
        } else {
            $data = $query->selectRaw('ROUND(latitude::numeric, 3) as latitude, ROUND(longitude::numeric, 3) as longitude, COUNT(*) as weight')
                ->groupBy(DB::raw('ROUND(latitude::numeric, 3), ROUND(longitude::numeric, 3)'))
                ->get()
                ->map(fn($item) => [
                    'latitude' => (float)$item->latitude,
                    'longitude' => (float)$item->longitude,
                    'weight' => (int)$item->weight,
                ])
                ->toArray();
        }

        return $this->success($data, 'Data heatmap berhasil diambil');
    }
}
