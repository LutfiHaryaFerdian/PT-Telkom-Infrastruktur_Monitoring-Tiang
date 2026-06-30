@extends('layouts.app')
@section('title','Tambah Tiang')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tiang.index') }}">Data Tiang</a></li>
<li class="breadcrumb-item active">Tambah Tiang</li>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Tambah Tiang Baru</h1>
</div>

<form method="POST" action="{{ route('tiang.store') }}" id="formTiang">
@csrf
<div class="row g-3">
    <!-- Kiri: Data Utama -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header py-3"><h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Data Lokasi & STO</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">District <span class="text-danger">*</span></label>
                        <select class="form-select" id="sel_district" required>
                            <option value="">— Pilih District —</option>
                            @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Area <span class="text-danger">*</span></label>
                        <select class="form-select" id="sel_area" disabled>
                            <option value="">— Pilih Area —</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">STO <span class="text-danger">*</span></label>
                        <select class="form-select" id="sel_sto" name="sto_id" required disabled>
                            <option value="">— Pilih STO —</option>
                        </select>
                        @error('sto_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nama Jalan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_jalan" class="form-control @error('nama_jalan') is-invalid @enderror"
                            value="{{ old('nama_jalan') }}" placeholder="Contoh: Jl. ZA Pagar Alam No. 12" required>
                        @error('nama_jalan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header py-3"><h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Koordinat GPS</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                        <input type="number" name="latitude" step="0.0000001" min="-7" max="-4"
                            class="form-control @error('latitude') is-invalid @enderror"
                            value="{{ old('latitude') }}" placeholder="-5.3456789" required>
                        <div class="form-text">Rentang: -7.0 s/d -4.0 (Lampung)</div>
                        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                        <input type="number" name="longitude" step="0.0000001" min="104" max="107"
                            class="form-control @error('longitude') is-invalid @enderror"
                            value="{{ old('longitude') }}" placeholder="105.2567890" required>
                        <div class="form-text">Rentang: 104.0 s/d 107.0 (Lampung)</div>
                        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div id="pick-map" style="height:220px;border-radius:10px;border:1px solid #dee2e6;"></div>
                        <div class="form-text"><i class="bi bi-cursor me-1"></i>Klik peta untuk mengisi koordinat otomatis</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header py-3"><h6 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Data Teknis</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jenis Tiang <span class="text-danger">*</span></label>
                        <select name="jenis_tiang_id" class="form-select @error('jenis_tiang_id') is-invalid @enderror" required>
                            <option value="">— Pilih —</option>
                            @foreach($jenisTiang as $j)
                            <option value="{{ $j->id }}" {{ old('jenis_tiang_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                            @endforeach
                        </select>
                        @error('jenis_tiang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kondisi Tiang <span class="text-danger">*</span></label>
                        <select name="kondisi_tiang_id" class="form-select @error('kondisi_tiang_id') is-invalid @enderror" required>
                            <option value="">— Pilih —</option>
                            @foreach($kondisiTiang as $k)
                            <option value="{{ $k->id }}" {{ old('kondisi_tiang_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                        @error('kondisi_tiang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Input <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_input" class="form-control @error('tgl_input') is-invalid @enderror"
                            value="{{ old('tgl_input', date('Y-m-d')) }}" required>
                        @error('tgl_input')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jml Tiang Operator Sekitar</label>
                        <input type="number" name="jml_tiang_operator_sekitar" class="form-control" min="0" value="{{ old('jml_tiang_operator_sekitar',0) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jml Kabel DC Telkom</label>
                        <input type="number" name="jml_kabel_dc_telkom" class="form-control" min="0" value="{{ old('jml_kabel_dc_telkom',0) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jml KU Telkom</label>
                        <input type="number" name="jml_ku_telkom" class="form-control" min="0" value="{{ old('jml_ku_telkom',0) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Teknisi</label>
                        <input type="text" name="nama_teknisi" class="form-control" value="{{ old('nama_teknisi') }}" placeholder="Nama teknisi yang menginput">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ID Tiang Instansi</label>
                        <input type="text" name="id_tiang_instansi" class="form-control @error('id_tiang_instansi') is-invalid @enderror"
                            value="{{ old('id_tiang_instansi') }}" placeholder="Opsional">
                        @error('id_tiang_instansi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Temuan</label>
                        <input type="date" name="tanggal_temuan" class="form-control" value="{{ old('tanggal_temuan') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanan: ISP Operator -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:70px">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-wifi me-2"></i>ISP Penumpang</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddOp">
                    <i class="bi bi-plus"></i> Tambah
                </button>
            </div>
            <div class="card-body p-0" id="operators-container">
                <div class="text-center text-muted py-3 small" id="op-empty">Belum ada ISP ditambahkan</div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-save me-2"></i>Simpan Tiang
                </button>
                <a href="{{ route('tiang.index') }}" class="btn btn-light w-100 mt-2">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>

<!-- Template ISP row -->
<template id="op-template">
    <div class="op-row border-bottom p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold text-primary" style="font-size: .85rem;"><i class="bi bi-wifi me-1"></i>Detail ISP</span>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-op"><i class="bi bi-trash"></i></button>
        </div>
        <div class="mb-2">
            <label class="form-label text-muted small mb-1">Operator</label>
            <select name="operators[__IDX__][operator_id]" class="form-select form-select-sm" required>
                <option value="">— Pilih Operator —</option>
                @foreach($operators as $op)
                <option value="{{ $op->id }}">{{ $op->nama_operator }}</option>
                @endforeach
            </select>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-4">
                <label class="form-label text-muted small mb-1" style="font-size:11px">Kabel DC</label>
                <input type="number" name="operators[__IDX__][jml_kabel_dc]" class="form-control form-control-sm" min="0" value="0">
            </div>
            <div class="col-4">
                <label class="form-label text-muted small mb-1" style="font-size:11px">Kabel KU</label>
                <input type="number" name="operators[__IDX__][jml_ku]" class="form-control form-control-sm" min="0" value="0">
            </div>
            <div class="col-4">
                <label class="form-label text-muted small mb-1" style="font-size:11px">ODP</label>
                <input type="number" name="operators[__IDX__][jml_odp]" class="form-control form-control-sm" min="0" value="0">
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label text-muted small mb-1">Status Legalitas</label>
            <select name="operators[__IDX__][status_legalitas]" class="form-select form-select-sm">
                <option value="perlu_verifikasi">Perlu Verifikasi</option>
                <option value="legal">Legal</option>
                <option value="ilegal">Ilegal</option>
            </select>
        </div>
        <div>
            <label class="form-label text-muted small mb-1">Keterangan</label>
            <textarea name="operators[__IDX__][keterangan]" class="form-control form-control-sm" rows="1" placeholder="Keterangan..."></textarea>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Cascade dropdown
document.getElementById('sel_district').addEventListener('change', async function() {
    const areaEl = document.getElementById('sel_area');
    const stoEl  = document.getElementById('sel_sto');
    areaEl.innerHTML = '<option value="">— Pilih Area —</option>';
    stoEl.innerHTML  = '<option value="">— Pilih STO —</option>';
    areaEl.disabled = stoEl.disabled = true;
    if (!this.value) return;

    const res = await fetch(`/api/master/areas?district_id=${this.value}`).then(r => r.json());
    res.data?.forEach(a => areaEl.add(new Option(a.name, a.id)));
    areaEl.disabled = false;
});

document.getElementById('sel_area').addEventListener('change', async function() {
    const stoEl = document.getElementById('sel_sto');
    stoEl.innerHTML = '<option value="">— Pilih STO —</option>';
    stoEl.disabled = true;
    if (!this.value) return;

    const res = await fetch(`/api/master/stos?area_id=${this.value}`).then(r => r.json());
    res.data?.forEach(s => stoEl.add(new Option(`${s.kode} — ${s.nama||''}`, s.id)));
    stoEl.disabled = false;
});

// Pick map
const pickMap = L.map('pick-map').setView([-5.35, 105.25], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(pickMap);
let pickMarker;
pickMap.on('click', e => {
    const { lat, lng } = e.latlng;
    document.querySelector('[name="latitude"]').value = lat.toFixed(7);
    document.querySelector('[name="longitude"]').value = lng.toFixed(7);
    if (pickMarker) pickMarker.setLatLng(e.latlng);
    else pickMarker = L.marker(e.latlng).addTo(pickMap);
});

// ISP operators
let opIdx = 0;
document.getElementById('btnAddOp').addEventListener('click', () => {
    const tpl = document.getElementById('op-template').innerHTML.replace(/__IDX__/g, opIdx++);
    document.getElementById('op-empty')?.remove();
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('operators-container').appendChild(div.firstElementChild);
});

document.getElementById('operators-container').addEventListener('click', e => {
    if (e.target.closest('.btn-remove-op')) {
        e.target.closest('.op-row').remove();
        if (!document.querySelectorAll('.op-row').length) {
            const empty = document.createElement('div');
            empty.id = 'op-empty';
            empty.className = 'text-center text-muted py-3 small';
            empty.textContent = 'Belum ada ISP ditambahkan';
            document.getElementById('operators-container').appendChild(empty);
        }
    }
});
</script>
@endpush
