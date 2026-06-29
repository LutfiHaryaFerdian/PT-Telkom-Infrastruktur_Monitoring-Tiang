@extends('layouts.app')
@section('title', 'Detail Tiang — ' . ($tiang->kode_tiang ?? $tiang->id))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tiang.index') }}">Data Tiang</a></li>
<li class="breadcrumb-item active">{{ $tiang->kode_tiang ?? 'Detail' }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@section('content')
<!-- Header -->
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="page-title">{{ $tiang->kode_tiang ?? 'Tiang #'.$tiang->id }}</h1>
        <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
            @php
                $lvl = $tiang->kondisiTiang?->level;
                $lvlClass = ['baik'=>'badge-baik','perlu_perhatian'=>'badge-perlu','rusak'=>'badge-rusak'][$lvl] ?? '';
                $stClass = ['ok'=>'badge-ok','pending'=>'badge-pending','ditolak'=>'badge-ditolak','double_input'=>'badge-double'][$tiang->status_verifikasi] ?? '';
            @endphp
            <span class="badge-status {{ $lvlClass }}">{{ $tiang->kondisiTiang?->nama ?? '—' }}</span>
            <span class="badge-status {{ $stClass }}">{{ ucfirst($tiang->status_verifikasi) }}</span>
            @if($tiang->has_anomali)
                <span class="badge-status badge-anomali"><i class="bi bi-exclamation-triangle-fill me-1"></i>Ada Anomali</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('update', $tiang)
        <a href="{{ route('tiang.edit', $tiang) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endcan
        @can('delete', $tiang)
        <form method="POST" action="{{ route('tiang.destroy', $tiang) }}" onsubmit="return confirm('Hapus tiang ini? Data tidak akan hilang permanen.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
        @endcan
        @if(auth()->user()->isAdmin())
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots me-1"></i>Lainnya
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Verifikasi</h6></li>
                @if($tiang->canTransitionTo('ok'))
                <li>
                    <button class="dropdown-item text-success" onclick="verifikasi('ok')">
                        <i class="bi bi-check-circle me-2"></i>Setujui (OK)
                    </button>
                </li>
                @endif
                @if($tiang->canTransitionTo('ditolak'))
                <li>
                    <button class="dropdown-item text-danger" onclick="verifikasi('ditolak')">
                        <i class="bi bi-x-circle me-2"></i>Tolak
                    </button>
                </li>
                @endif
                @if($tiang->canTransitionTo('pending'))
                <li>
                    <button class="dropdown-item text-warning" onclick="verifikasi('pending')">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Kembalikan ke Pending
                    </button>
                </li>
                @endif
            </ul>
        </div>
        @endif
    </div>
</div>

<div class="row g-3">
    <!-- Kiri: Info Utama -->
    <div class="col-lg-8">

        <!-- Info Dasar -->
        <div class="card mb-3">
            <div class="card-header py-3"><h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Tiang</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                    $info = [
                        'Kode Tiang' => $tiang->kode_tiang ?? '—',
                        'ID Instansi' => $tiang->id_tiang_instansi ?? '—',
                        'STO' => $tiang->sto?->kode . ' — ' . ($tiang->sto?->nama ?? ''),
                        'Jenis Tiang' => $tiang->jenisTiang?->nama ?? '—',
                        'Nama Jalan' => $tiang->nama_jalan,
                        'District' => $tiang->sto?->area?->district?->name ?? '—',
                        'Area' => $tiang->sto?->area?->name ?? '—',
                        'Nama Teknisi' => $tiang->nama_teknisi ?? '—',
                        'Jml Tiang Sekitar' => $tiang->jml_tiang_operator_sekitar,
                        'Jml Kabel DC Telkom' => $tiang->jml_kabel_dc_telkom,
                        'Jml KU Telkom' => $tiang->jml_ku_telkom,
                        'Tgl Input' => $tiang->tgl_input?->format('d/m/Y') ?? '—',
                        'Tanggal Temuan' => $tiang->tanggal_temuan?->format('d/m/Y') ?? '—',
                        'Dibuat Oleh' => $tiang->createdBy?->name ?? '—',
                        'Diperbarui' => $tiang->updatedBy?->name ?? '—',
                    ];
                    @endphp
                    @foreach($info as $label => $value)
                    <div class="col-md-4 col-6">
                        <div class="text-muted" style="font-size:.77rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em">{{ $label }}</div>
                        <div style="font-size:.9rem;font-weight:500;margin-top:.15rem">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Foto Tiang -->
        <div class="card mb-3">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-images me-2"></i>Foto Tiang</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach(['depan','kanan','kiri'] as $jenis)
                    @php $foto = $tiang->fotoTiang->firstWhere('jenis_foto', $jenis); @endphp
                    <div class="col-md-4">
                        <div class="border rounded p-2 text-center" style="background:#f8f9fa">
                            <div class="text-muted small fw-semibold mb-2 text-uppercase">{{ $jenis }}</div>
                            @if($foto)
                                <img src="{{ asset('storage/'.$foto->path_file) }}" class="img-fluid rounded" style="max-height:160px;object-fit:cover;cursor:pointer"
                                    onclick="window.open(this.src)" alt="Foto {{ $jenis }}">
                            @else
                                <div style="height:120px;display:grid;place-items:center;color:#adb5bd">
                                    <div><i class="bi bi-image fs-3 d-block mb-1"></i><small>Belum ada foto</small></div>
                                </div>
                            @endif
                            <!-- Upload form -->
                            <form method="POST" action="/api/tiang/{{ $tiang->id }}/foto" enctype="multipart/form-data" class="mt-2 upload-foto-form">
                                @csrf
                                <input type="hidden" name="jenis_foto" value="{{ $jenis }}">
                                <input type="file" name="foto" accept="image/*" class="form-control form-control-sm" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary mt-1 w-100" style="font-size:.75rem">
                                    <i class="bi bi-upload me-1"></i>{{ $foto ? 'Ganti' : 'Upload' }}
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ISP Penumpang -->
        <div class="card mb-3">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-wifi me-2"></i>ISP Penumpang</h6>
            </div>
            <div class="card-body p-0">
                @if($tiang->tiangOperator->isEmpty())
                    <div class="text-center text-muted py-4 small">Tidak ada ISP penumpang</div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:.85rem">
                        <thead><tr>
                            <th>Operator</th><th>Kabel DC</th><th>KU</th><th>ODP</th><th>Status</th>
                            @if(auth()->user()->isAdmin())<th>Aksi</th>@endif
                        </tr></thead>
                        <tbody>
                        @foreach($tiang->tiangOperator as $pivot)
                        <tr>
                            <td class="fw-semibold">{{ $pivot->operator?->nama_operator ?? '—' }}</td>
                            <td>{{ $pivot->jml_kabel_dc }}</td>
                            <td>{{ $pivot->jml_ku }}</td>
                            <td>{{ $pivot->jml_odp }}</td>
                            <td>
                                @php $legalClass = ['legal'=>'badge-ok','ilegal'=>'badge-ditolak','perlu_verifikasi'=>'badge-pending'][$pivot->status_legalitas]??''; @endphp
                                <span class="badge-status {{ $legalClass }}">{{ ucfirst(str_replace('_',' ',$pivot->status_legalitas)) }}</span>
                            </td>
                            @if(auth()->user()->isAdmin())
                            <td>
                                <select class="form-select form-select-sm" style="width:140px" onchange="updateLegalitas({{ $tiang->id }}, {{ $pivot->operator_id }}, this.value)">
                                    @foreach(['legal','ilegal','perlu_verifikasi'] as $s)
                                    <option value="{{ $s }}" {{ $pivot->status_legalitas === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <!-- Anomali -->
        @if($tiang->activeAnomalyLogs->isNotEmpty())
        <div class="card mb-3 border-danger">
            <div class="card-header py-3" style="background:#fff0f0">
                <h6 class="mb-0 text-danger"><i class="bi bi-bug me-2"></i>Anomali Aktif ({{ $tiang->activeAnomalyLogs->count() }})</h6>
            </div>
            <div class="card-body p-0">
                @foreach($tiang->activeAnomalyLogs as $anomali)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-danger mb-1" style="font-size:.7rem">{{ str_replace('_',' ',strtoupper($anomali->jenis_anomali)) }}</span>
                            <div style="font-size:.85rem">{{ $anomali->keterangan }}</div>
                            <div class="text-muted" style="font-size:.77rem">Terdeteksi: {{ $anomali->detected_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <form method="POST" action="/api/anomali/{{ $anomali->id }}/resolve">
                            @csrf
                            <button type="button" onclick="resolveAnomali({{ $anomali->id }}, this)" class="btn btn-sm btn-outline-success" style="font-size:.75rem">
                                Selesaikan
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Inspeksi -->
        <div class="card mb-3">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Riwayat Inspeksi</h6>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalInspeksi">
                    <i class="bi bi-plus me-1"></i>Tambah Inspeksi
                </button>
            </div>
            <div class="card-body p-0">
                @forelse($tiang->inspections as $insp)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-semibold" style="font-size:.88rem">{{ $insp->inspected_at?->format('d/m/Y H:i') }}</div>
                            <div class="text-muted" style="font-size:.8rem">Oleh: {{ $insp->inspectedBy?->name ?? 'Tidak diketahui' }}</div>
                            <div style="font-size:.82rem">Kondisi: <span class="fw-semibold">{{ $insp->kondisiTiang?->nama ?? '—' }}</span></div>
                            @if($insp->catatan)<div class="text-muted" style="font-size:.8rem">{{ $insp->catatan }}</div>@endif
                            @if($insp->hasCoordinateDifference())
                            <div class="alert alert-warning py-1 px-2 mt-1 mb-0" style="font-size:.77rem">
                                <i class="bi bi-geo me-1"></i>Koordinat inspeksi berbeda dari data tiang
                                <form method="POST" action="{{ route('inspection.apply-koordinat', $insp) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-warning ms-2" style="font-size:.72rem;padding:.15rem .4rem">Terapkan ke Tiang</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('inspection.show', $insp) }}" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">Belum ada riwayat inspeksi</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kanan: Peta & Info -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body p-2">
                <div id="detail-map" style="height:250px;border-radius:8px;"></div>
                <div class="mt-2 text-center text-muted" style="font-size:.8rem">
                    <i class="bi bi-geo-alt me-1"></i>{{ $tiang->latitude }}, {{ $tiang->longitude }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Inspeksi -->
<div class="modal fade" id="modalInspeksi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Inspeksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('inspection.store', $tiang) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kondisi <span class="text-danger">*</span></label>
                            <select name="kondisi_tiang_id" class="form-select" required>
                                @foreach($kondisiTiang as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Inspeksi <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="inspected_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Latitude (Opsional)</label>
                            <input type="number" name="latitude" step="0.0000001" class="form-control" placeholder="{{ $tiang->latitude }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Longitude (Opsional)</label>
                            <input type="number" name="longitude" step="0.0000001" class="form-control" placeholder="{{ $tiang->longitude }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan hasil inspeksi..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Foto (Maks 10 foto, 5MB/foto)</label>
                            <input type="file" name="fotos[]" class="form-control" multiple accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Inspeksi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Detail map
const map = L.map('detail-map').setView([{{ $tiang->latitude }}, {{ $tiang->longitude }}], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.marker([{{ $tiang->latitude }}, {{ $tiang->longitude }}])
    .addTo(map)
    .bindPopup('{{ $tiang->kode_tiang }}').openPopup();

// Verifikasi
async function verifikasi(status) {
    if (!confirm(`Ubah status verifikasi menjadi "${status}"?`)) return;
    const res = await fetch(`/api/tiang/{{ $tiang->id }}/verifikasi`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status })
    });
    const json = await res.json();
    if (json.success) location.reload();
    else alert(json.message);
}

// Update legalitas
async function updateLegalitas(tiangId, opId, status) {
    const res = await fetch(`/api/tiang/${tiangId}/isp/${opId}/legalitas`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status_legalitas: status })
    });
    const json = await res.json();
    if (!json.success) alert(json.message);
}

// Resolve anomali
async function resolveAnomali(id, btn) {
    if (!confirm('Tandai anomali ini sebagai selesai?')) return;
    const res = await fetch(`/api/anomali/${id}/resolve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });
    const json = await res.json();
    if (json.success) location.reload();
    else alert(json.message);
}

// Upload foto (AJAX)
document.querySelectorAll('.upload-foto-form').forEach(form => {
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(form);
        const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
        const json = await res.json();
        if (json.success) location.reload();
        else alert(json.message || 'Gagal upload foto');
    });
});
</script>
@endpush
