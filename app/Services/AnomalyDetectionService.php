<?php

namespace App\Services;

use App\Models\AnomalyLog;
use App\Models\FotoTiang;
use App\Models\TiangTelekomunikasi;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnomalyDetectionService
{
    /**
     * Jalankan semua pengecekan anomali untuk satu tiang.
     *
     * Trigger: Create, Update, Restore tiang; setiap perubahan tiang_operator.
     * Seluruh method dijalankan dalam DB::transaction().
     *
     * Duplikat anomali aktif dicegah oleh PARTIAL UNIQUE INDEX.
     * Saat insert melanggar → tangkap UniqueConstraintViolationException, abaikan.
     *
     * has_anomali hanya diubah di sini — tidak ada endpoint/form lain.
     * Gunakan saveQuietly() agar tidak memicu event → tidak loop.
     */
    public function detect(TiangTelekomunikasi $tiang): void
    {
        DB::transaction(function () use ($tiang) {
            // Muat relasi yang dibutuhkan
            $tiang->loadMissing(['kondisiTiang', 'tiangOperator.operator', 'fotoTiang']);

            $this->checkDoubleInput($tiang);
            $this->checkKoordinatTidakValid($tiang);
            $this->checkKondisiNok($tiang);
            $this->checkIspTidakTeridentifikasi($tiang);
            $this->checkVerifikasiPending($tiang);
            $this->checkDataTidakLengkap($tiang);

            // Update has_anomali berdasarkan apakah masih ada anomali aktif
            $hasAnomali = AnomalyLog::where('tiang_id', $tiang->id)
                                    ->where('status', 'aktif')
                                    ->exists();

            // saveQuietly() → tidak trigger event/observer → tidak loop
            $tiang->has_anomali = $hasAnomali;
            $tiang->saveQuietly();
        });
    }

    /**
     * 1. DOUBLE INPUT: koordinat terlalu dekat dengan tiang lain yang aktif.
     * Threshold: ABS(lat-?) < 0.0001 AND ABS(lng-?) < 0.0001
     */
    protected function checkDoubleInput(TiangTelekomunikasi $tiang): void
    {
        $duplicate = TiangTelekomunikasi::where('id', '!=', $tiang->id)
            ->whereNull('deleted_at')
            ->whereRaw('ABS(latitude - ?) < 0.0001', [$tiang->latitude])
            ->whereRaw('ABS(longitude - ?) < 0.0001', [$tiang->longitude])
            ->first();

        if ($duplicate) {
            $this->insertAnomali(
                $tiang->id,
                'double_input',
                "Koordinat tiang terlalu dekat dengan tiang {$duplicate->kode_tiang} (ID: {$duplicate->id}). "
                . "Jarak lat: " . abs($tiang->latitude - $duplicate->latitude)
                . ", lng: " . abs($tiang->longitude - $duplicate->longitude)
            );
        }
    }

    /**
     * 2. KOORDINAT TIDAK VALID: di luar batas wilayah Lampung.
     * lat OUT OF (-7.0 .. -4.0) OR lng OUT OF (104.0 .. 107.0)
     */
    protected function checkKoordinatTidakValid(TiangTelekomunikasi $tiang): void
    {
        $latInvalid = $tiang->latitude < -7.0 || $tiang->latitude > -4.0;
        $lngInvalid = $tiang->longitude < 104.0 || $tiang->longitude > 107.0;

        if ($latInvalid || $lngInvalid) {
            $details = [];
            if ($latInvalid) {
                $details[] = "latitude ({$tiang->latitude}) di luar rentang -7.0 s/d -4.0";
            }
            if ($lngInvalid) {
                $details[] = "longitude ({$tiang->longitude}) di luar rentang 104.0 s/d 107.0";
            }

            $this->insertAnomali(
                $tiang->id,
                'koordinat_tidak_valid',
                'Koordinat tiang berada di luar wilayah Lampung: ' . implode(', ', $details)
            );
        }
    }

    /**
     * 3. KONDISI NOK: kondisi tiang level perlu_perhatian atau rusak.
     */
    protected function checkKondisiNok(TiangTelekomunikasi $tiang): void
    {
        $kondisi = $tiang->kondisiTiang;

        if ($kondisi && in_array($kondisi->level, ['perlu_perhatian', 'rusak'])) {
            $this->insertAnomali(
                $tiang->id,
                'kondisi_nok',
                "Kondisi tiang \"{$kondisi->nama}\" (level: {$kondisi->level}) memerlukan perhatian."
            );
        }
    }

    /**
     * 4. ISP TIDAK TERIDENTIFIKASI:
     * Operator non-predefined yang tidak memiliki keterangan atau keterangannya mengandung
     * 'tidak diketahui' atau 'no label'.
     */
    protected function checkIspTidakTeridentifikasi(TiangTelekomunikasi $tiang): void
    {
        $unknownOperators = $tiang->tiangOperator
            ->filter(function ($pivot) {
                if ($pivot->operator && $pivot->operator->is_predefined) {
                    return false; // operator predefined, skip
                }

                $ket = strtolower($pivot->keterangan_operator ?? '');
                return empty(trim($pivot->keterangan_operator ?? ''))
                    || str_contains($ket, 'tidak diketahui')
                    || str_contains($ket, 'no label');
            });

        if ($unknownOperators->isNotEmpty()) {
            $namaList = $unknownOperators
                ->map(fn ($p) => $p->operator?->nama_operator ?? 'Operator #' . $p->operator_id)
                ->implode(', ');

            $this->insertAnomali(
                $tiang->id,
                'isp_tidak_teridentifikasi',
                "Terdapat ISP non-predefined yang tidak teridentifikasi: {$namaList}."
            );
        }
    }

    /**
     * 5. VERIFIKASI PENDING > 3 HARI:
     * status_verifikasi='pending' AND tgl_input < NOW() - INTERVAL '3 days'
     */
    protected function checkVerifikasiPending(TiangTelekomunikasi $tiang): void
    {
        if ($tiang->status_verifikasi === 'pending'
            && $tiang->tgl_input
            && $tiang->tgl_input->lt(now()->subDays(3))
        ) {
            $hariLewat = $tiang->tgl_input->diffInDays(now());

            $this->insertAnomali(
                $tiang->id,
                'verifikasi_pending',
                "Tiang belum diverifikasi selama {$hariLewat} hari sejak tanggal input ({$tiang->tgl_input->format('d/m/Y')})."
            );
        }
    }

    /**
     * 6. DATA TIDAK LENGKAP:
     * nama_teknisi kosong OR nama_jalan kosong OR tidak ada foto tiang.
     * Sebutkan field mana yang kosong.
     */
    protected function checkDataTidakLengkap(TiangTelekomunikasi $tiang): void
    {
        $missing = [];

        if (empty(trim($tiang->nama_teknisi ?? ''))) {
            $missing[] = 'nama teknisi';
        }

        if (empty(trim($tiang->nama_jalan ?? ''))) {
            $missing[] = 'nama jalan';
        }

        $jumlahFoto = FotoTiang::where('tiang_id', $tiang->id)->count();
        if ($jumlahFoto === 0) {
            $missing[] = 'foto tiang (belum ada foto)';
        }

        if (! empty($missing)) {
            $this->insertAnomali(
                $tiang->id,
                'data_tidak_lengkap',
                'Data tiang tidak lengkap — field kosong: ' . implode(', ', $missing) . '.'
            );
        }
    }

    /**
     * Insert anomali ke database.
     * Jika PARTIAL UNIQUE INDEX dilanggar → UniqueConstraintViolationException → abaikan.
     */
    protected function insertAnomali(int $tiangId, string $jenis, string $keterangan): void
    {
        try {
            AnomalyLog::create([
                'tiang_id'    => $tiangId,
                'jenis_anomali' => $jenis,
                'keterangan'  => $keterangan,
                'status'      => 'aktif',
                'detected_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Anomali jenis ini sudah aktif untuk tiang ini → abaikan, bukan error
            Log::debug("AnomalyDetectionService: duplikat anomali diabaikan", [
                'tiang_id' => $tiangId,
                'jenis'    => $jenis,
            ]);
        }
    }
}
