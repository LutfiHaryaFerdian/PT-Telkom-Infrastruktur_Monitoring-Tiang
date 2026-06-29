@extends('layouts.app')
@section('title', 'Detail Inspeksi — ' . $inspection->id)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tiang.index') }}">Data Tiang</a></li>
<li class="breadcrumb-item"><a href="{{ route('tiang.show', $inspection->tiang_id) }}">{{ $inspection->tiang?->kode_tiang ?? 'Tiang' }}</a></li>
<li class="breadcrumb-item active">Inspeksi #{{ $inspection->id }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Detail Inspeksi #{{ $inspection->id }}</h1>
        <p class="page-breadcrumb mb-0">Dilakukan pada {{ $inspection->inspected_at?->format('d/m/Y H:i') }} oleh {{ $inspection->inspectedBy?->name ?? '—' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tiang.show', $inspection->tiang_id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Tiang
        </a>
        <form method="POST" action="{{ route('inspection.destroy', $inspection) }}" onsubmit="return confirm('Hapus riwayat inspeksi ini beserta seluruh foto terkait?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
    </div>
</div>

<div class="row g-3">
    <!-- Kiri: Detail -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Hasil Inspeksi</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Kondisi Saat Inspeksi</div>
                        <div class="mt-1">
                            @php
                                $lvl = $inspection->kondisiTiang?->level;
                                $lvlClass = ['baik'=>'badge-baik','perlu_perhatian'=>'badge-perlu','rusak'=>'badge-rusak'][$lvl] ?? '';
                            @endphp
                            <span class="badge-status {{ $lvlClass }}">{{ $inspection->kondisiTiang?->nama ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Tanggal & Waktu</div>
                        <div class="mt-1 fw-semibold">{{ $inspection->inspected_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold text-uppercase">Catatan Lapangan</div>
                        <div class="mt-1 p-3 bg-light rounded" style="font-size:.9rem;white-space:pre-wrap">{{ $inspection->catatan ?? 'Tidak ada catatan.' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Foto Inspeksi -->
        <div class="card mb-3">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-images me-2"></i>Foto Lapangan ({{ $inspection->fotoInspeksi->count() }})</h6>
            </div>
            <div class="card-body">
                @if($inspection->fotoInspeksi->isEmpty())
                    <div class="text-center text-muted py-4 small">Tidak ada foto diupload untuk inspeksi ini.</div>
                @else
                    <div class="row g-3">
                        @foreach($inspection->fotoInspeksi as $foto)
                        <div class="col-md-4 col-6">
                            <div class="border rounded p-2 text-center bg-light">
                                <img src="{{ asset('storage/'.$foto->path_file) }}" class="img-fluid rounded" style="max-height:180px;object-fit:cover;cursor:pointer"
                                    onclick="window.open(this.src)" alt="Foto Lapangan">
                                <div class="text-muted small mt-1 text-truncate" title="{{ $foto->original_filename }}">{{ $foto->original_filename }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kanan: Map Perbandingan Koordinat -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Lokasi Temuan</h6>
            </div>
            <div class="card-body p-2">
                @if($inspection->latitude && $inspection->longitude)
                    <div id="inspection-map" style="height:250px;border-radius:8px;"></div>
                    <div class="mt-2 text-center text-muted small">
                        <div>Koordinat: {{ $inspection->latitude }}, {{ $inspection->longitude }}</div>
                        @if($hasDiff)
                            <div class="alert alert-warning py-1 px-2 mt-2 mb-0" style="font-size:.77rem">
                                <i class="bi bi-exclamation-triangle me-1"></i>Posisi berbeda dari posisi asal tiang.
                                <form method="POST" action="{{ route('inspection.apply-koordinat', $inspection) }}" class="mt-1">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-warning w-100" style="font-size:.75rem">Terapkan ke Koordinat Tiang</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center text-muted py-5 small">
                        <i class="bi bi-geo-alt fs-3 d-block mb-1"></i>Tidak ada koordinat GPS tercatat
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($inspection->latitude && $inspection->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('inspection-map').setView([{{ $inspection->latitude }}, {{ $inspection->longitude }}], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// Marker lokasi inspeksi
L.marker([{{ $inspection->latitude }}, {{ $inspection->longitude }}], {
    icon: L.divIcon({
        className: '',
        html: `<div style="width:16px;height:16px;border-radius:50%;background:#e8402a;border:3px solid #fff;box-shadow:0 1px 6px rgba(0,0,0,.4)"></div>`,
        iconSize: [16, 16], iconAnchor: [8, 8]
    })
}).addTo(map).bindPopup('Lokasi Temuan Inspeksi').openPopup();

@if($inspection->tiang && $hasDiff)
// Marker lokasi tiang asal (jika berbeda)
L.marker([{{ $inspection->tiang->latitude }}, {{ $inspection->tiang->longitude }}], {
    icon: L.divIcon({
        className: '',
        html: `<div style="width:14px;height:14px;border-radius:50%;background:#6c757d;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>`,
        iconSize: [14, 14], iconAnchor: [7, 7]
    })
}).addTo(map).bindPopup('Koordinat Terdaftar Tiang: {{ $inspection->tiang->kode_tiang }}');

// Tarik garis perbandingan
L.polyline([
    [{{ $inspection->latitude }}, {{ $inspection->longitude }}],
    [{{ $inspection->tiang->latitude }}, {{ $inspection->tiang->longitude }}]
], { color: '#ffc107', dashArray: '5, 5' }).addTo(map);
@endif
</script>
@endif
@endpush
