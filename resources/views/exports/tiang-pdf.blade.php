<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Tiang Telekomunikasi</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1a3a5c; }
        .header p { margin: 5px 0 0; color: #666; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; color: #1a3a5c; }
        tr:nth-child(even) { background-color: #fafafa; }
        .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }
        .badge-baik { background-color: #d1fae5; color: #065f46; }
        .badge-perlu { background-color: #fef3c7; color: #92400e; }
        .badge-rusak { background-color: #fee2e2; color: #991b1b; }
        .badge-yes { background-color: #fee2e2; color: #991b1b; }
        .badge-no { color: #666; font-weight: normal; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Data Tiang Telekomunikasi</h2>
        <p>PT Telkom Infrastruktur Indonesia — District Lampung<br>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px">No</th>
                <th>Kode Tiang</th>
                <th>ID Instansi</th>
                <th>STO</th>
                <th>Jenis Tiang</th>
                <th>Kondisi</th>
                <th>Jalan</th>
                <th style="width: 30px">Anomali</th>
                <th>Tgl Input</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tiang as $index => $t)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="font-weight: bold">{{ $t->kode_tiang }}</td>
                <td>{{ $t->id_tiang_instansi ?? '—' }}</td>
                <td>{{ $t->sto?->kode }}</td>
                <td>{{ $t->jenisTiang?->nama }}</td>
                <td>
                    @php
                        $lvl = $t->kondisiTiang?->level;
                        $class = ['baik'=>'badge-baik','perlu_perhatian'=>'badge-perlu','rusak'=>'badge-rusak'][$lvl] ?? '';
                    @endphp
                    <span class="badge {{ $class }}">{{ $t->kondisiTiang?->nama }}</span>
                </td>
                <td>{{ $t->nama_jalan }}</td>
                <td style="text-align: center">
                    @if($t->has_anomali)
                        <span class="badge badge-yes">Ya</span>
                    @else
                        <span class="badge badge-no">Tidak</span>
                    @endif
                </td>
                <td>{{ $t->tgl_input?->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Halaman 1 dari 1 — Dokumen ini dihasilkan secara otomatis oleh Sistem Monitoring Tiang Web GIS.
    </div>
</body>
</html>
