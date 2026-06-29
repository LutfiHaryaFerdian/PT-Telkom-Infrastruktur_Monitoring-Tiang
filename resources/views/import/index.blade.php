@extends('layouts.app')
@section('title', 'Import Data')
@section('breadcrumb')
<li class="breadcrumb-item active">Import Excel</li>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Import Data Tiang</h1>
    <p class="page-breadcrumb mb-0">Upload berkas Excel (.xlsx) untuk menambahkan data tiang secara massal</p>
</div>

<div class="row g-3">
    <!-- Form Upload -->
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-upload me-2"></i>Upload File Excel</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <div class="form-text">Maksimal ukuran file: 20MB. Format yang didukung: <code>.xlsx</code> dan <code>.xls</code>.</div>
                    </div>
                    <div class="form-check mb-4">
                        <input type="checkbox" name="create_master" value="1" class="form-check-input" id="create_master" checked>
                        <label class="form-check-label form-label mb-0" for="create_master">
                            Buat Master Data Otomatis
                        </label>
                        <div class="form-text">Jika diaktifkan, sistem akan otomatis membuat data District, Area, atau STO baru jika kode STO tidak terdaftar di master data.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Mulai Import
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3"><h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Panduan Format Kolom</h6></div>
            <div class="card-body" style="font-size:.82rem">
                <p>Pastikan file Excel memiliki header di baris pertama dengan nama persis berikut:</p>
                <table class="table table-sm table-bordered mb-0">
                    <thead><tr class="table-light"><th>Header Kolom</th><th>Wajib</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        <tr><td><code>STO</code></td><td>Ya</td><td>Kode STO (e.g. KDT, TBU, dll)</td></tr>
                        <tr><td><code>Koordinat Tiang</code></td><td>Ya</td><td>Format: <code>latitude,longitude</code> (e.g. <code>-5.3821,105.2523</code>)</td></tr>
                        <tr><td><code>Nama Jalan</code></td><td>Ya</td><td>Lokasi detail nama jalan tiang berada.</td></tr>
                        <tr><td><code>ID Tiang Instansi</code></td><td>Tidak</td><td>ID tiang instansi/PLN (jika ada)</td></tr>
                        <tr><td><code>Kondisi Tiang</code></td><td>Tidak</td><td>Nama kondisi tiang. Default: <code>Baik. Cat OK</code></td></tr>
                        <tr><td><code>Jenis Tiang</code></td><td>Tidak</td><td>Nama jenis tiang. Default: <code>T-7-2 Seqment</code></td></tr>
                        <tr><td><code>Jml Tiang Sekitar</code></td><td>Tidak</td><td>Angka bulat. Default: 0</td></tr>
                        <tr><td><code>Jml Kabel DC Telkom</code></td><td>Tidak</td><td>Angka bulat. Default: 0</td></tr>
                        <tr><td><code>Jml KU Telkom</code></td><td>Tidak</td><td>Angka bulat. Default: 0</td></tr>
                        <tr><td><code>Teknisi</code></td><td>Tidak</td><td>Nama teknisi penginput.</td></tr>
                        <tr><td><code>Tgl Input</code></td><td>Tidak</td><td>Format tanggal valid (YYYY-MM-DD) atau Serial Date Excel.</td></tr>
                        <tr><td><code>Nama Operator Lain</code></td><td>Tidak</td><td>Nama-nama ISP dipisahkan koma (e.g. <code>Moratel, Biznet</code>)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Import -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header py-3"><h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Import Terakhir</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.875rem">
                        <thead><tr>
                            <th>File</th><th>Status</th><th>Progress</th><th>Detail Baris</th><th>Tanggal</th><th>Aksi</th>
                        </tr></thead>
                        <tbody>
                        @forelse($histories as $h)
                        <tr>
                            <td class="fw-semibold text-truncate" style="max-width:160px" title="{{ $h->filename }}">{{ $h->filename }}</td>
                            <td>
                                @php
                                    $stClass = [
                                        'processing' => 'bg-warning text-dark',
                                        'done'       => 'bg-success text-white',
                                        'failed'     => 'bg-danger text-white'
                                    ][$h->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $stClass }}">{{ ucfirst($h->status) }}</span>
                            </td>
                            <td>
                                <div class="progress" style="height:6px;width:80px">
                                    <div class="progress-bar {{ $h->status==='failed'?'bg-danger':($h->status==='done'?'bg-success':'bg-warning') }}" style="width:{{ $h->progress_percent }}%"></div>
                                </div>
                                <span class="small text-muted" style="font-size:.7rem">{{ $h->progress_percent }}%</span>
                            </td>
                            <td class="small text-muted">
                                <i class="bi bi-check-circle text-success"></i> {{ $h->success_rows }}<br>
                                <i class="bi bi-x-circle text-danger"></i> {{ $h->failed_rows }}
                            </td>
                            <td class="text-muted small">{{ $h->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('import.show', $h) }}" class="btn btn-sm btn-outline-primary" style="padding:.2rem .4rem;font-size:.78rem">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat import</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $histories->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
