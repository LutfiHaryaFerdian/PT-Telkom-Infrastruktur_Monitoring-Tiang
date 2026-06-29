<?php

namespace App\Http\Controllers;

use App\Http\Requests\TiangRequest;
use App\Models\ActivityLog;
use App\Models\Area;
use App\Models\District;
use App\Models\JenisTiang;
use App\Models\KondisiTiang;
use App\Models\OperatorIsp;
use App\Models\Sto;
use App\Models\TiangTelekomunikasi;
use App\Models\TiangOperator;
use App\Services\AnomalyDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TiangController extends Controller
{
    public function __construct(protected AnomalyDetectionService $anomalyService) {}

    // ============================================================
    // INDEX — AJAX server-side DataTables-compatible
    // ============================================================

    public function index(): View
    {
        $districts = District::orderBy('name')->get();
        $jenisTiang = JenisTiang::orderBy('nama')->get();
        $kondisiTiang = KondisiTiang::orderBy('nama')->get();
        return view('tiang.index', compact('districts', 'jenisTiang', 'kondisiTiang'));
    }

    /**
     * AJAX endpoint DataTables server-side.
     * Response: { draw, recordsTotal, recordsFiltered, data }
     */
    public function data(Request $request): JsonResponse
    {
        $query = TiangTelekomunikasi::with([
            'sto:id,kode,nama',
            'jenisTiang:id,nama',
            'kondisiTiang:id,nama,level',
        ])->whereNull('deleted_at');

        // Filter dari DataTables
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('kode_tiang ILIKE ?', ["%$search%"])
                  ->orWhereRaw('nama_jalan ILIKE ?', ["%$search%"])
                  ->orWhereRaw('nama_teknisi ILIKE ?', ["%$search%"]);
            });
        }

        // Filter custom
        if ($request->filled('filter_district')) {
            $query->whereHas('sto.area', fn($q) => $q->where('district_id', $request->filter_district));
        }
        if ($request->filled('filter_sto')) {
            $query->where('sto_id', $request->filter_sto);
        }
        if ($request->filled('filter_kondisi')) {
            $query->whereHas('kondisiTiang', fn($q) => $q->where('level', $request->filter_kondisi));
        }
        if ($request->filled('filter_status')) {
            $query->where('status_verifikasi', $request->filter_status);
        }
        if ($request->filled('filter_anomali')) {
            $query->where('has_anomali', $request->filter_anomali === '1');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('tgl_input', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl_input', '<=', $request->date_to);
        }

        $total    = TiangTelekomunikasi::whereNull('deleted_at')->count();
        $filtered = $query->count();

        // Sorting
        $columns = ['id', 'kode_tiang', 'nama_jalan', 'tgl_input', 'status_verifikasi', 'has_anomali'];
        $sortIdx  = (int)$request->input('order.0.column', 0);
        $sortDir  = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $sortCol  = $columns[$sortIdx] ?? 'id';

        $data = $query->orderBy($sortCol, $sortDir)
            ->skip((int)$request->input('start', 0))
            ->take(max(10, min(100, (int)$request->input('length', 10))))
            ->get();

        return response()->json([
            'draw'            => (int)$request->input('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    // ============================================================
    // CREATE
    // ============================================================

    public function create(): View
    {
        $districts  = District::orderBy('name')->get();
        $jenisTiang = JenisTiang::orderBy('nama')->get();
        $kondisiTiang = KondisiTiang::orderBy('nama')->get();
        $operators  = OperatorIsp::whereNull('deleted_at')->orderBy('nama_operator')->get();
        return view('tiang.create', compact('districts', 'jenisTiang', 'kondisiTiang', 'operators'));
    }

    // ============================================================
    // STORE
    // ============================================================

    public function store(TiangRequest $request): RedirectResponse
    {
        $this->authorize('create', TiangTelekomunikasi::class);

        $tiang = DB::transaction(function () use ($request) {
            $sto = Sto::findOrFail($request->sto_id);

            // Generate kode_tiang dalam transaction dengan locking
            // Lock parent Sto row to prevent race conditions on code generation
            Sto::where('id', $sto->id)->lockForUpdate()->first();
            $last = TiangTelekomunikasi::where('sto_id', $sto->id)->max('kode_tiang');

            $lastNum = $last ? (int)substr($last, -5) : 0;
            $next    = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
            $kode    = "TI-{$sto->kode}-{$next}";

            $tiang = TiangTelekomunikasi::create(array_merge(
                $request->validated(),
                [
                    'kode_tiang'  => $kode,
                    'created_by'  => auth()->id(),
                    'status_verifikasi' => 'pending',
                ]
            ));

            // Simpan operator ISP jika ada
            if ($request->filled('operators')) {
                foreach ($request->operators as $op) {
                    TiangOperator::create([
                        'tiang_id'            => $tiang->id,
                        'operator_id'         => $op['operator_id'],
                        'jml_kabel_dc'        => $op['jml_kabel_dc'] ?? 0,
                        'jml_ku'              => $op['jml_ku'] ?? 0,
                        'jml_odp'             => $op['jml_odp'] ?? 0,
                        'keterangan_operator' => $op['keterangan'] ?? null,
                        'status_legalitas'    => $op['status_legalitas'] ?? 'perlu_verifikasi',
                    ]);
                }
            }

            return $tiang;
        });

        // Catat activity log
        $user = auth()->user();
        ActivityLog::record(
            'tiang', $tiang->id, 'created',
            "{$user->getRoleDisplayName()} menambahkan tiang baru {$tiang->kode_tiang} di {$tiang->nama_jalan}",
            null, $tiang->toArray()
        );

        // Deteksi anomali
        $this->anomalyService->detect($tiang->load(['kondisiTiang', 'tiangOperator.operator', 'fotoTiang']));

        return redirect()->route('tiang.show', $tiang)
            ->with('success', "Tiang {$tiang->kode_tiang} berhasil ditambahkan.");
    }

    // ============================================================
    // SHOW
    // ============================================================

    public function show(TiangTelekomunikasi $tiang): View
    {
        $tiang->load([
            'sto.area.district',
            'jenisTiang',
            'kondisiTiang',
            'createdBy',
            'updatedBy',
            'fotoTiang',
            'tiangOperator.operator',
            'inspections' => fn($q) => $q->with(['inspectedBy', 'kondisiTiang', 'fotoInspeksi'])->latest('inspected_at'),
            'activeAnomalyLogs',
            'anomalyLogs' => fn($q) => $q->latest('detected_at')->limit(10),
        ]);

        $kondisiTiang = KondisiTiang::orderBy('nama')->get();
        $operators    = OperatorIsp::whereNull('deleted_at')->orderBy('nama_operator')->get();
        $allOperatorIds = $tiang->tiangOperator->pluck('operator_id');
        $availableOperators = $operators->whereNotIn('id', $allOperatorIds);

        return view('tiang.show', compact('tiang', 'kondisiTiang', 'operators', 'availableOperators'));
    }

    // ============================================================
    // EDIT
    // ============================================================

    public function edit(TiangTelekomunikasi $tiang): View
    {
        $this->authorize('update', $tiang);

        $tiang->load(['sto.area.district', 'tiangOperator.operator']);
        $districts  = District::orderBy('name')->get();
        $jenisTiang = JenisTiang::orderBy('nama')->get();
        $kondisiTiang = KondisiTiang::orderBy('nama')->get();
        $operators  = OperatorIsp::whereNull('deleted_at')->orderBy('nama_operator')->get();

        return view('tiang.edit', compact('tiang', 'districts', 'jenisTiang', 'kondisiTiang', 'operators'));
    }

    // ============================================================
    // UPDATE
    // ============================================================

    public function update(TiangRequest $request, TiangTelekomunikasi $tiang): RedirectResponse
    {
        $this->authorize('update', $tiang);

        $old = $tiang->toArray();

        DB::transaction(function () use ($request, $tiang) {
            $tiang->update(array_merge(
                $request->validated(),
                ['updated_by' => auth()->id()]
            ));

            // Sync operator ISP
            if ($request->has('operators')) {
                // Hapus semua operator lama, lalu insert ulang
                $tiang->tiangOperator()->delete();
                foreach ($request->operators as $op) {
                    TiangOperator::create([
                        'tiang_id'            => $tiang->id,
                        'operator_id'         => $op['operator_id'],
                        'jml_kabel_dc'        => $op['jml_kabel_dc'] ?? 0,
                        'jml_ku'              => $op['jml_ku'] ?? 0,
                        'jml_odp'             => $op['jml_odp'] ?? 0,
                        'keterangan_operator' => $op['keterangan'] ?? null,
                        'status_legalitas'    => $op['status_legalitas'] ?? 'perlu_verifikasi',
                    ]);
                }
            }
        });

        $user = auth()->user();
        ActivityLog::record(
            'tiang', $tiang->id, 'updated',
            "{$user->getRoleDisplayName()} memperbarui data tiang {$tiang->kode_tiang}",
            $old, $tiang->fresh()->toArray()
        );

        $this->anomalyService->detect($tiang->fresh()->load(['kondisiTiang', 'tiangOperator.operator', 'fotoTiang']));

        return redirect()->route('tiang.show', $tiang)
            ->with('success', "Tiang {$tiang->kode_tiang} berhasil diperbarui.");
    }

    // ============================================================
    // DELETE (soft)
    // ============================================================

    public function destroy(TiangTelekomunikasi $tiang): RedirectResponse
    {
        $this->authorize('delete', $tiang);

        $kodeTiang = $tiang->kode_tiang;
        $tiang->delete(); // File foto TIDAK ikut dihapus

        $user = auth()->user();
        ActivityLog::record(
            'tiang', $tiang->id, 'deleted',
            "{$user->getRoleDisplayName()} menghapus tiang {$kodeTiang}"
        );

        return redirect()->route('tiang.index')
            ->with('success', "Tiang {$kodeTiang} berhasil dihapus (soft delete).");
    }

    // ============================================================
    // TRASHED
    // ============================================================

    public function trashed(): View
    {
        $tiangTrashed = TiangTelekomunikasi::onlyTrashed()
            ->with(['sto:id,kode', 'kondisiTiang:id,nama,level', 'createdBy:id,name'])
            ->latest('deleted_at')
            ->paginate(20);

        return view('tiang.trashed', compact('tiangTrashed'));
    }

    // ============================================================
    // RESTORE
    // ============================================================

    public function restore(int $tiang): RedirectResponse
    {
        $tiangModel = TiangTelekomunikasi::onlyTrashed()->findOrFail($tiang);
        $this->authorize('restore', $tiangModel);

        $tiangModel->restore();

        $user = auth()->user();
        ActivityLog::record(
            'tiang', $tiangModel->id, 'restored',
            "{$user->getRoleDisplayName()} memulihkan tiang {$tiangModel->kode_tiang}"
        );

        // Trigger anomaly detection setelah restore
        $this->anomalyService->detect($tiangModel->fresh()->load(['kondisiTiang', 'tiangOperator.operator', 'fotoTiang']));

        return back()->with('success', "Tiang {$tiangModel->kode_tiang} berhasil dipulihkan.");
    }
}
