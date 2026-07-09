<?php

namespace App\Http\Controllers;

use App\Http\Requests\IspBalasanRequest;
use App\Models\IspBalasan;
use App\Models\IspSurat;
use App\Models\TiangOperator;
use App\Models\ActivityLog;
use App\Services\TindakLanjutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IspBalasanController extends Controller
{
    public function __construct(protected TindakLanjutService $tindakLanjutService) {}

    public function store(IspBalasanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $surat = IspSurat::with('tiangOperator.operator', 'tiangOperator.tiang')->findOrFail($data['isp_surat_id']);
        $tiangOperator = $surat->tiangOperator;

        if ($request->hasFile('file_balasan')) {
            $file = $request->file('file_balasan');
            $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
            if ($realMime !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya file PDF yang diterima.'], 422);
            }

            $ext = $file->getClientOriginalExtension() ?: 'pdf';
            $uuid = Str::uuid();
            $filename = "{$uuid}.{$ext}";
            $path = $file->storeAs("tindaklanjut/{$tiangOperator->id}/balasan", $filename, 'public');
            $data['file_balasan'] = Storage::url($path);
        }

        $data['dicatat_oleh'] = auth()->id();
        $balasan = IspBalasan::create($data);

        // Update status tindak lanjut otomatis
        $this->tindakLanjutService->updateStatus($tiangOperator);

        // Catat activity log
        $user = auth()->user();
        ActivityLog::record(
            'isp_balasan',
            $balasan->id,
            'balasan_created',
            "{$user->getRoleDisplayName()} {$user->name} menambahkan balasan surat untuk ISP {$tiangOperator->operator?->nama_operator} di tiang {$tiangOperator->tiang?->kode_tiang}",
            null,
            $balasan->toArray()
        );

        return response()->json([
            'success' => true,
            'data' => $balasan,
            'message' => 'Balasan berhasil disimpan.'
        ]);
    }

    public function destroy(IspBalasan $ispBalasan): JsonResponse
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Admin.');
        }

        $surat = IspSurat::with('tiangOperator.operator', 'tiangOperator.tiang')->findOrFail($ispBalasan->isp_surat_id);
        $tiangOperator = $surat->tiangOperator;

        if ($ispBalasan->file_balasan) {
            $relativeDiskPath = str_replace('/storage/', '', $ispBalasan->file_balasan);
            Storage::disk('public')->delete($relativeDiskPath);
        }

        $oldValues = $ispBalasan->toArray();
        $ispBalasan->delete();

        // Update status tindak lanjut otomatis
        $this->tindakLanjutService->updateStatus($tiangOperator);

        // Catat activity log
        $user = auth()->user();
        ActivityLog::record(
            'isp_balasan',
            $oldValues['id'],
            'balasan_deleted',
            "{$user->getRoleDisplayName()} {$user->name} menghapus balasan surat untuk ISP {$tiangOperator->operator?->nama_operator} di tiang {$tiangOperator->tiang?->kode_tiang}",
            $oldValues,
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil dihapus.'
        ]);
    }
}
