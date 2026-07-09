<?php

namespace App\Http\Controllers;

use App\Http\Requests\IspSuratRequest;
use App\Models\IspSurat;
use App\Models\TiangOperator;
use App\Models\ActivityLog;
use App\Services\TindakLanjutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IspSuratController extends Controller
{
    public function __construct(protected TindakLanjutService $tindakLanjutService) {}

    public function store(IspSuratRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tiangOperator = TiangOperator::with(['operator', 'tiang'])->findOrFail($data['tiang_operator_id']);

        if ($request->hasFile('file_surat')) {
            $file = $request->file('file_surat');
            $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
            if ($realMime !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya file PDF yang diterima.'], 422);
            }

            $ext = $file->getClientOriginalExtension() ?: 'pdf';
            $uuid = Str::uuid();
            $filename = "{$uuid}.{$ext}";
            $path = $file->storeAs("tindaklanjut/{$tiangOperator->id}/surat", $filename, 'public');
            $data['file_surat'] = Storage::url($path);
        }

        $data['dikirim_oleh'] = auth()->id();
        $surat = IspSurat::create($data);

        // Update status tindak lanjut otomatis
        $this->tindakLanjutService->updateStatus($tiangOperator);

        // Catat activity log
        $user = auth()->user();
        ActivityLog::record(
            'isp_surat',
            $surat->id,
            'surat_created',
            "{$user->getRoleDisplayName()} {$user->name} menambahkan surat untuk ISP {$tiangOperator->operator?->nama_operator} di tiang {$tiangOperator->tiang?->kode_tiang}",
            null,
            $surat->toArray()
        );

        return response()->json([
            'success' => true,
            'data' => $surat,
            'message' => 'Surat berhasil disimpan.'
        ]);
    }

    public function destroy(IspSurat $ispSurat): JsonResponse
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Admin.');
        }

        $tiangOperator = TiangOperator::with(['operator', 'tiang'])->findOrFail($ispSurat->tiang_operator_id);

        if ($ispSurat->file_surat) {
            $relativeDiskPath = str_replace('/storage/', '', $ispSurat->file_surat);
            Storage::disk('public')->delete($relativeDiskPath);
        }

        $oldValues = $ispSurat->toArray();
        $ispSurat->delete();

        // Update status tindak lanjut otomatis
        $this->tindakLanjutService->updateStatus($tiangOperator);

        // Catat activity log
        $user = auth()->user();
        ActivityLog::record(
            'isp_surat',
            $oldValues['id'],
            'surat_deleted',
            "{$user->getRoleDisplayName()} {$user->name} menghapus surat untuk ISP {$tiangOperator->operator?->nama_operator} di tiang {$tiangOperator->tiang?->kode_tiang}",
            $oldValues,
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Surat berhasil dihapus.'
        ]);
    }
}
