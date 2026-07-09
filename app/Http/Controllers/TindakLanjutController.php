<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\OperatorIsp;
use App\Models\TiangOperator;
use App\Models\TiangTelekomunikasi;
use App\Models\ActivityLog;
use App\Services\TindakLanjutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TindakLanjutController extends Controller
{
    public function __construct(protected TindakLanjutService $tindakLanjutService) {}

    public function index(): View
    {
        $districts = District::orderBy('name')->get();
        $operators = OperatorIsp::orderBy('nama_operator')->get();

        // Ambil data stats awal secara global
        $stats = [
            'belum_disurati' => TiangOperator::where('status_tindaklanjut', 'belum_disurati')->count(),
            'perlu_followup' => TiangOperator::where('status_tindaklanjut', 'perlu_followup')->count(),
            'menunggu_balasan' => TiangOperator::where('status_tindaklanjut', 'sudah_disurati')->count(),
            'selesai' => TiangOperator::where('status_tindaklanjut', 'selesai')->count(),
        ];

        return view('tindaklanjut.index', compact('districts', 'operators', 'stats'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = TiangOperator::with([
            'tiang:id,kode_tiang,nama_jalan,sto_id',
            'tiang.sto:id,kode,nama',
            'tiang.sto.area:id,name,district_id',
            'tiang.sto.area.district:id,name',
            'operator:id,nama_operator',
            'ispSurat',
            'ispFollowup'
        ]);

        // Filter dari search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->whereHas('tiang', function ($q) use ($search) {
                $q->whereRaw('kode_tiang ILIKE ?', ["%$search%"])
                  ->orWhereRaw('nama_jalan ILIKE ?', ["%$search%"]);
            });
        }

        // Custom Filters
        if ($request->filled('filter_district')) {
            $query->whereHas('tiang.sto.area', fn($q) => $q->where('district_id', $request->filter_district));
        }
        if ($request->filled('filter_area')) {
            $query->whereHas('tiang.sto', fn($q) => $q->where('area_id', $request->filter_area));
        }
        if ($request->filled('filter_sto')) {
            $query->whereHas('tiang', fn($q) => $q->where('sto_id', $request->filter_sto));
        }
        if ($request->filled('filter_operator')) {
            $query->where('operator_id', $request->filter_operator);
        }
        if ($request->filled('filter_status_tindaklanjut')) {
            $query->where('status_tindaklanjut', $request->filter_status_tindaklanjut);
        }

        // Hitung total dan filtered
        $total = TiangOperator::count();
        $filtered = $query->count();

        // Hitung stats berdasarkan query terfilter (tapi tanpa limit/offset)
        $statsQuery = clone $query;
        $statsData = $statsQuery->select('status_tindaklanjut', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status_tindaklanjut')
            ->pluck('total', 'status_tindaklanjut')
            ->toArray();

        $stats = [
            'belum_disurati' => $statsData['belum_disurati'] ?? 0,
            'perlu_followup' => $statsData['perlu_followup'] ?? 0,
            'menunggu_balasan' => $statsData['sudah_disurati'] ?? 0,
            'selesai' => $statsData['selesai'] ?? 0,
        ];

        // Sorting
        $sortIdx = (int)$request->input('order.0.column', 0);
        $sortDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // Custom order join to handle sorting easily
        $query->join('tiang_telekomunikasi', 'tiang_telekomunikasi.id', '=', 'tiang_operator.tiang_id')
              ->join('operator_isp', 'operator_isp.id', '=', 'tiang_operator.operator_id')
              ->join('stos', 'stos.id', '=', 'tiang_telekomunikasi.sto_id');

        $sortCol = 'tiang_operator.id';
        if ($sortIdx === 0) $sortCol = 'tiang_telekomunikasi.kode_tiang';
        elseif ($sortIdx === 1) $sortCol = 'stos.kode';
        elseif ($sortIdx === 2) $sortCol = 'tiang_telekomunikasi.nama_jalan';
        elseif ($sortIdx === 3) $sortCol = 'operator_isp.nama_operator';
        elseif ($sortIdx === 4) $sortCol = 'tiang_operator.status_legalitas';
        elseif ($sortIdx === 5) $sortCol = 'tiang_operator.status_tindaklanjut';
        elseif ($sortIdx === 6) $sortCol = 'tiang_operator.tindaklanjut_updated_at';

        $data = $query->select('tiang_operator.*')
            ->orderBy($sortCol, $sortDir)
            ->skip((int)$request->input('start', 0))
            ->take(max(10, min(100, (int)$request->input('length', 10))))
            ->get();

        // Transform data untuk format DataTables
        $transformedData = $data->map(function ($row) {
            $suratTerakhir = $row->ispSurat->sortByDesc('tanggal_surat')->first();
            return [
                'id' => $row->id,
                'kode_tiang' => $row->tiang?->kode_tiang ?? '—',
                'sto' => $row->tiang?->sto?->kode ?? '—',
                'nama_jalan' => $row->tiang?->nama_jalan ?? '—',
                'nama_operator' => $row->operator?->nama_operator ?? '—',
                'status_legalitas' => $row->status_legalitas,
                'status_tindaklanjut' => $row->status_tindaklanjut,
                'status_tindaklanjut_label' => $row->status_tindaklanjut_label,
                'surat_terakhir' => $suratTerakhir ? $suratTerakhir->tanggal_surat->format('Y-m-d') : 'Belum disurati',
                'action_url' => route('tindaklanjut.show', $row->id),
            ];
        });

        return response()->json([
            'draw'            => (int)$request->input('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $transformedData,
            'stats'           => $stats,
        ]);
    }

    public function show(TiangOperator $tiangOperator): View
    {
        $tiangOperator->load([
            'tiang:id,kode_tiang,nama_jalan,sto_id',
            'tiang.sto:id,kode,nama',
            'operator:id,nama_operator',
            'ispSurat.dikirimOleh',
            'ispSurat.ispBalasan.dicatatOleh',
            'ispFollowup.dilakukanOleh'
        ]);

        return view('tindaklanjut.show', compact('tiangOperator'));
    }

    public function selesai(TiangOperator $tiangOperator): JsonResponse
    {
        $this->authorizeAdmin();

        $oldStatus = $tiangOperator->status_tindaklanjut;
        $tiangOperator->status_tindaklanjut = 'selesai';
        $tiangOperator->tindaklanjut_updated_at = now();
        $tiangOperator->save();

        $user = auth()->user();
        ActivityLog::record(
            'tiang_operator',
            $tiangOperator->id,
            'tindaklanjut_completed',
            "{$user->getRoleDisplayName()} {$user->name} menyelesaikan tindak lanjut untuk ISP {$tiangOperator->operator?->nama_operator} di tiang {$tiangOperator->tiang?->kode_tiang}",
            ['status_tindaklanjut' => $oldStatus],
            ['status_tindaklanjut' => 'selesai']
        );

        $this->tindakLanjutService->updateStatus($tiangOperator);

        return response()->json(['success' => true, 'message' => 'Status tindak lanjut berhasil ditandai selesai.']);
    }

    public function reset(TiangOperator $tiangOperator): JsonResponse
    {
        $this->authorizeAdmin();

        $oldStatus = $tiangOperator->status_tindaklanjut;
        $tiangOperator->status_tindaklanjut = 'belum_disurati';
        $tiangOperator->save();

        $this->tindakLanjutService->updateStatus($tiangOperator);
        $newStatus = $tiangOperator->fresh()->status_tindaklanjut;

        $user = auth()->user();
        ActivityLog::record(
            'tiang_operator',
            $tiangOperator->id,
            'tindaklanjut_reset',
            "{$user->getRoleDisplayName()} {$user->name} mereset tindak lanjut untuk ISP {$tiangOperator->operator?->nama_operator} di tiang {$tiangOperator->tiang?->kode_tiang}",
            ['status_tindaklanjut' => $oldStatus],
            ['status_tindaklanjut' => $newStatus]
        );

        return response()->json(['success' => true, 'message' => 'Status tindak lanjut berhasil di-reset.']);
    }

    public function timelinePartial(TiangOperator $tiangOperator): View
    {
        $tiangOperator->load([
            'ispSurat.dikirimOleh',
            'ispSurat.ispBalasan.dicatatOleh',
            'ispFollowup.dilakukanOleh'
        ]);

        return view('tindaklanjut.partials.timeline', compact('tiangOperator'));
    }

    public function apiIspStatus(TiangTelekomunikasi $tiang): JsonResponse
    {
        $tiang->load([
            'tiangOperator.operator',
            'tiangOperator.ispSurat.ispBalasan'
        ]);

        $ispList = $tiang->tiangOperator->map(function ($row) {
            $suratTerakhir = $row->ispSurat->sortByDesc('tanggal_surat')->first();
            $adaBalasan = $row->ispSurat->flatMap->ispBalasan->isNotEmpty();
            return [
                'operator_id' => $row->operator_id,
                'nama_operator' => $row->operator?->nama_operator ?? '—',
                'status_legalitas' => $row->status_legalitas,
                'status_tindaklanjut' => $row->status_tindaklanjut,
                'surat_terakhir' => $suratTerakhir ? $suratTerakhir->tanggal_surat->format('Y-m-d') : null,
                'ada_balasan' => $adaBalasan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tiang_id' => $tiang->id,
                'kode_tiang' => $tiang->kode_tiang,
                'isp_list' => $ispList
            ],
            'message' => 'Status ISP berhasil dimuat'
        ]);
    }

    protected function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Admin.');
        }
    }
}
