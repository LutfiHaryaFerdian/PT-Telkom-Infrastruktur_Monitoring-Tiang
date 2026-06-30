<?php

namespace App\Jobs;

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
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;

class ImportTiangJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum attempts before marking as failed.
     */
    public int $tries = 3;

    /**
     * Timeout in seconds.
     */
    public int $timeout = 900; // 15 menit

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $importHistoryId,
        public readonly string $filePath,
        public readonly bool $createMaster
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Tingkatkan memory limit untuk parsing Excel ukuran besar
        ini_set('memory_limit', '2048M');

        $history = ImportHistory::find($this->importHistoryId);
        if (!$history) {
            return;
        }

        try {
            if (!SimpleXLSX::parse($this->filePath)) {
                throw new \Exception("Gagal membaca file Excel: " . SimpleXLSX::parseError());
            }
            $xlsx = SimpleXLSX::parse($this->filePath);
            $rows = $xlsx->rows();

            if (count($rows) < 2) {
                $history->update(['status' => 'failed', 'finished_at' => now()]);
                return;
            }

            // Baris pertama = header (di baris index 0 pada SimpleXLSX)
            $headers    = array_map('trim', $rows[0]);
            $headerMap  = array_flip($headers);
            $dataRows   = array_slice($rows, 1);

            $total   = count($dataRows);
            $success = 0;
            $failed  = 0;

            $history->update(['total_rows' => $total]);

            foreach ($dataRows as $rowNum => $row) {
                $actualRow = $rowNum + 2; // +2 karena header di baris 0

                try {
                    $rawData = [];
                    foreach ($headers as $idx => $hName) {
                        if (!empty($hName)) {
                            $rawData[$hName] = $row[$idx] ?? '';
                        }
                    }

                    $wasProcessed = $this->processRow(
                        $history, $actualRow, $rawData, $headerMap, $row, $this->createMaster
                    );

                    if ($wasProcessed) {
                        $success++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    ImportHistoryError::create([
                        'import_history_id' => $history->id,
                        'row_number'        => $actualRow,
                        'error_message'     => $e->getMessage(),
                        'raw_data'          => isset($rawData) ? $rawData : [],
                    ]);
                    Log::warning("Import row #{$actualRow} error: {$e->getMessage()}");
                }

                // Update progress setiap 50 baris
                if ($actualRow % 50 === 0) {
                    $percent = (int)(($actualRow / $total) * 100);
                    $history->update([
                        'progress_percent' => $percent,
                        'success_rows'     => $success,
                        'failed_rows'      => $failed,
                    ]);
                }
            }

            $history->update([
                'status'           => 'done',
                'progress_percent' => 100,
                'success_rows'     => $success,
                'failed_rows'      => $failed,
                'finished_at'      => now(),
            ]);

            // Dispatch anomaly detection untuk tiang yang baru diimport
            RunAnomalyDetectionJob::dispatch($history->id);

        } catch (\Throwable $e) {
            $history->update(['status' => 'failed', 'finished_at' => now()]);
            Log::error("Import gagal total: {$e->getMessage()}");
        } finally {
            // Hapus file sementara
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        }
    }

    /**
     * Proses satu baris data.
     */
    protected function processRow(
        ImportHistory $history,
        int $rowNum,
        array $rawData,
        array $headerMap,
        array $row,
        bool $createMaster
    ): bool {
        $get = function(string $key) use ($headerMap, $row) {
            $aliases = [
                'Koordinat Tiang'            => ['Koordinat Tiang', 'koordinat Tiang'],
                'Nama Jalan'                 => ['Nama Jalan', 'Data Lokasi/Nama Cluster/Nama Ruas/Nama Jalan'],
                'Jml Tiang Sekitar'          => ['Jml Tiang Sekitar', 'Jumlah Tiang Operator lain (yang berada disekitar Tiang Telkom)'],
                'Jml Kabel DC Telkom'       => ['Jml Kabel DC Telkom', 'Jumlah Kabel DC Telkom'],
                'Jml KU Telkom'             => ['Jml KU Telkom', 'Jumlah KU Telkom'],
                'Verifikasi AOM'             => ['Verifikasi AOM', 'Verifikasi Admin AOM'],
                'Teknisi'                    => ['Teknisi', 'Nama Teknisi Inputer'],
                'Nama Operator Lain'         => ['Nama Operator Lain', 'Nama Operator Lain (Yang menumpang di Tiang Telkom)'],
                'Jml Kabel DC Operator Lain' => ['Jml Kabel DC Operator Lain', 'Jumlah Kabel DC Operator Lain di Tiang Telkom'],
                'Jml KU Operator Lain'       => ['Jml KU Operator Lain', 'Jumlah KU Operator Lain di Tiang Telkom'],
                'Jml ODP Operator Lain'      => ['Jml ODP Operator Lain', 'Jumlah ODP Operator Lain di Tiang Telkom'],
                'Keterangan Operator'        => ['Keterangan Operator', 'Keterangan Nama Operator (Ketikkan jika dipilih "LAINNYA")'],
            ];

            $searchKeys = isset($aliases[$key]) ? $aliases[$key] : [$key];

            foreach ($searchKeys as $sKey) {
                if (isset($headerMap[$sKey])) {
                    return trim($row[$headerMap[$sKey]] ?? '');
                }
            }

            return '';
        };

        // === VALIDASI WAJIB ===
        $stoKode   = strtoupper($get('STO'));
        $koordinat = $get('Koordinat Tiang');
        $namaJalan = $get('Nama Jalan');

        // Jika baris kosong (STO, Koordinat, dan Nama Jalan kosong), abaikan silently
        if (empty($stoKode) && empty($koordinat) && empty($namaJalan)) {
            return false;
        }

        if (empty($stoKode)) {
            throw new \InvalidArgumentException('Kolom STO wajib diisi.');
        }
        if (empty($koordinat)) {
            throw new \InvalidArgumentException('Kolom Koordinat Tiang wajib diisi.');
        }
        if (empty($namaJalan)) {
            throw new \InvalidArgumentException('Kolom Nama Jalan wajib diisi.');
        }

        // === KOORDINAT ===
        $parts = array_map('trim', explode(',', $koordinat));
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Format koordinat tidak valid: '{$koordinat}'. Gunakan: latitude,longitude");
        }
        [$lat, $lng] = [(float)$parts[0], (float)$parts[1]];

        if ($lat < -7.0 || $lat > -4.0) {
            throw new \InvalidArgumentException("Latitude {$lat} di luar rentang wilayah Lampung (-7.0 s/d -4.0).");
        }
        if ($lng < 104.0 || $lng > 107.0) {
            throw new \InvalidArgumentException("Longitude {$lng} di luar rentang wilayah Lampung (104.0 s/d 107.0).");
        }

        // === WRAP INDIVIDUAL ROW IN A DATABASE TRANSACTION ===
        return DB::transaction(function () use ($stoKode, $createMaster, $get, $jenisTiangNama, $kondisiNama, $makeInt, $tglInputRaw, $statusVerifikasi, $lat, $lng, $namaJalan, $history) {
            // === STO ===
            $sto = Sto::whereNull('deleted_at')->where('kode', $stoKode)->first();

            if (! $sto) {
                if (! $createMaster) {
                    throw new \InvalidArgumentException("STO '{$stoKode}' tidak ditemukan. Gunakan opsi '--create-master' untuk membuat otomatis.");
                }

                // Buat District → Area → STO jika belum ada
                $districtName = $get('District') ?: 'Lampung';
                $areaName     = $get('Area') ?: 'Lampung';

                $district = District::firstOrCreate(['name' => $districtName], ['name' => $districtName]);
                $area     = Area::firstOrCreate(
                    ['district_id' => $district->id, 'name' => $areaName],
                    ['district_id' => $district->id, 'name' => $areaName]
                );
                $sto = Sto::create(['area_id' => $area->id, 'kode' => $stoKode]);
            }

            // Lock parent Sto row to prevent race conditions on code generation
            Sto::where('id', $sto->id)->lockForUpdate()->first();

            // === JENIS TIANG ===
            $jenisTiang = JenisTiang::where('nama', $jenisTiangNama)->first()
                ?? JenisTiang::first(); // fallback ke pertama

            // === KONDISI TIANG ===
            $kondisiTiang = KondisiTiang::where('nama', $kondisiNama)->first()
                ?? KondisiTiang::first(); // fallback ke pertama

            // === JUMLAH NUMERIK ===
            $jmlSekitar  = $makeInt($get('Jml Tiang Sekitar'));
            $jmlKabelDc  = $makeInt($get('Jml Kabel DC Telkom'));
            $jmlKuTelkom = $makeInt($get('Jml KU Telkom'));

            // === TANGGAL INPUT ===
            $tglInput = null;
            if (! empty($tglInputRaw)) {
                // Handle Excel date serial (float)
                if (is_numeric($tglInputRaw)) {
                    $timestamp = ((float)$tglInputRaw - 25569) * 86400;
                    $tglInput = date('Y-m-d', $timestamp);
                } else {
                    try {
                        $tglInput = \Carbon\Carbon::parse($tglInputRaw)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tglInput = now()->format('Y-m-d');
                    }
                }
            } else {
                $tglInput = now()->format('Y-m-d');
            }

            // === GENERATE KODE TIANG ===
            $last = TiangTelekomunikasi::where('sto_id', $sto->id)->max('kode_tiang');
            $lastNum = $last ? (int)substr($last, -5) : 0;
            $kode = "TI-{$sto->kode}-" . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);

            $idInstansi = $get('ID Tiang Instansi') ?: null;

            $tiang = TiangTelekomunikasi::create([
                'kode_tiang'                => $kode,
                'id_tiang_instansi'         => $idInstansi,
                'sto_id'                    => $sto->id,
                'jenis_tiang_id'            => $jenisTiang->id,
                'kondisi_tiang_id'          => $kondisiTiang->id,
                'latitude'                  => $lat,
                'longitude'                 => $lng,
                'nama_jalan'                => $namaJalan,
                'jml_tiang_operator_sekitar'=> $jmlSekitar,
                'jml_kabel_dc_telkom'       => $jmlKabelDc,
                'jml_ku_telkom'             => $jmlKuTelkom,
                'nama_teknisi'              => $get('Teknisi') ?: null,
                'tgl_input'                 => $tglInput,
                'tanggal_temuan'            => null,
                'status_verifikasi'         => $statusVerifikasi,
                'created_by'                => $history->uploaded_by,
            ]);

            // === OPERATOR ISP ===
            $namaOperatorRaw  = $get('Nama Operator Lain');
            $jmlKabelDcOp     = max(0, (int)$get('Jml Kabel DC Operator Lain'));
            $jmlKuOp          = max(0, (int)$get('Jml KU Operator Lain'));
            $jmlOdpOp         = max(0, (int)$get('Jml ODP Operator Lain'));
            $keteranganOp     = $get('Keterangan Operator') ?: null;

            if (! empty($namaOperatorRaw)) {
                $operatorNames = array_unique(array_filter(array_map('trim', explode(',', $namaOperatorRaw))));

                foreach ($operatorNames as $nama) {
                    if (empty($nama)) continue;

                    $operator = OperatorIsp::withTrashed()
                        ->where('nama_operator', $nama)
                        ->first();

                    if (! $operator) {
                        $operator = OperatorIsp::create(['nama_operator' => $nama, 'is_predefined' => false]);
                    } elseif ($operator->trashed()) {
                        $operator->restore();
                    }

                    TiangOperator::firstOrCreate(
                        ['tiang_id' => $tiang->id, 'operator_id' => $operator->id],
                        [
                            'jml_kabel_dc'        => $jmlKabelDcOp,
                            'jml_ku'              => $jmlKuOp,
                            'jml_odp'             => $jmlOdpOp,
                            'keterangan_operator' => $keteranganOp,
                            'status_legalitas'    => 'perlu_verifikasi',
                        ]
                    );
                }
            }

            return true;
        }, 3);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ImportTiangJob gagal untuk import #{$this->importHistoryId}: {$exception->getMessage()}");

        ImportHistory::where('id', $this->importHistoryId)->update([
            'status' => 'failed',
            'finished_at' => now()
        ]);

        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
