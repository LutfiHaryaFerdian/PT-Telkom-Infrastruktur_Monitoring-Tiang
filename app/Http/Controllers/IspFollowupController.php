<?php

namespace App\Http\Controllers;

use App\Http\Requests\IspFollowupRequest;
use App\Models\IspFollowup;
use App\Models\TiangOperator;
use App\Models\ActivityLog;
use App\Services\TindakLanjutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IspFollowupController extends Controller
{
    public function __construct(protected TindakLanjutService $tindakLanjutService) {}

    public function store(IspFollowupRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tiangOperator = TiangOperator::with(['operator', 'tiang'])->findOrFail($data['tiang_operator_id']);

        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
            $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($realMime, $allowedMimes)) {
                return response()->json(['success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya gambar JPG/PNG dan file PDF yang diterima.'], 422);
            }

            $ext = $file->getClientOriginalExtension() ?: 'png';
            $uuid = Str::uuid();
            $filename = "{$uuid}.{$ext}";
            $path = $file->storeAs("tindaklanjut/{$tiangOperator->id}/followup", $filename, 'public');
            $data['file_bukti'] = Storage::url($path);
        }

        $data['dilakukan_oleh'] = auth()->id();
        $followup = IspFollowup::create($data);

        // Update status tindak lanjut otomatis
        $this->tindakLanjutService->updateStatus($tiangOperator);

        // Catat activity log
        $user = auth()->user();
        ActivityLog::record(
            'isp_followup',
            $followup->id,
            'followup_created',
            "{$user->getRoleDisplayName()} {$user->name} menambahkan followup untuk ISP {$tiangOperator->operator?->nama_operator} di tiang {$tiangOperator->tiang?->kode_tiang}",
            null,
            $followup->toArray()
        );

        return response()->json([
            'success' => true,
            'data' => $followup,
            'message' => 'Followup berhasil disimpan.'
        ]);
    }

    public function destroy(IspFollowup $ispFollowup): JsonResponse
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Admin.');
        }

        $tiangOperator = TiangOperator::with(['operator', 'tiang'])->findOrFail($ispFollowup->tiang_operator_id);

        if ($ispFollowup->file_bukti) {
            $relativeDiskPath = str_replace('/storage/', '', $ispFollowup->file_bukti);
            Storage::disk('public')->delete($relativeDiskPath);
        }

        $oldValues = $ispFollowup->toArray();
        $ispFollowup->delete();

        // Update status tindak lanjut otomatis
        $this->tindakLanjutService->updateStatus($tiangOperator);

        // Catat activity log
        $user = auth()->user();
        ActivityLog::record(
            'isp_followup',
            $oldValues['id'],
            'followup_deleted',
            "{$user->getRoleDisplayName()} {$user->name} menghapus followup untuk ISP {$tiangOperator->operator?->nama_operator} di tiang {$tiangOperator->tiang?->kode_tiang}",
            $oldValues,
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Followup berhasil dihapus.'
        ]);
    }
}
