@extends('layouts.app')
@section('title','Edit Tiang — '.$tiang->kode_tiang)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tiang.index') }}">Data Tiang</a></li>
<li class="breadcrumb-item"><a href="{{ route('tiang.show', $tiang) }}">{{ $tiang->kode_tiang }}</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header"><h1 class="page-title">Edit Tiang — {{ $tiang->kode_tiang }}</h1></div>

<form method="POST" action="{{ route('tiang.update', $tiang) }}" id="formTiang">
@csrf @method('PUT')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header py-3"><h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Data Lokasi & STO</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">District</label>
                        <select class="form-select" id="sel_district">
                            <option value="">— Pilih —</option>
                            @foreach($districts as $d)
                            <option value="{{ $d->id }}" {{ $tiang->sto?->area?->district_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Area</label>
                        <select class="form-select" id="sel_area">
                            <option value="{{ $tiang->sto?->area_id }}">{{ $tiang->sto?->area?->name ?? '—' }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">STO <span class="text-danger">*</span></label>
                        <select class="form-select" name="sto_id" id="sel_sto" required>
                            <option value="{{ $tiang->sto_id }}">{{ $tiang->sto?->kode }} — {{ $tiang->sto?->nama }}</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nama Jalan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_jalan" class="form-control" value="{{ old('nama_jalan', $tiang->nama_jalan) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                        <input type="number" name="latitude" step="0.0000001" class="form-control" value="{{ old('latitude', $tiang->latitude) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                        <input type="number" name="longitude" step="0.0000001" class="form-control" value="{{ old('longitude', $tiang->longitude) }}" required>
                    </div>
                    <div class="col-12">
                        <div id="pick-map" style="height:200px;border-radius:10px;border:1px solid #dee2e6;"></div>
                        <div class="form-text">Klik peta untuk mengisi koordinat otomatis</div>
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
                        <select name="jenis_tiang_id" class="form-select" required>
                            @foreach($jenisTiang as $j)
                            <option value="{{ $j->id }}" {{ $tiang->jenis_tiang_id == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kondisi Tiang <span class="text-danger">*</span></label>
                        <select name="kondisi_tiang_id" class="form-select" required>
                            @foreach($kondisiTiang as $k)
                            <option value="{{ $k->id }}" {{ $tiang->kondisi_tiang_id == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Input <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_input" class="form-control" value="{{ old('tgl_input', $tiang->tgl_input?->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jml Tiang Sekitar</label>
                        <input type="number" name="jml_tiang_operator_sekitar" class="form-control" min="0" value="{{ $tiang->jml_tiang_operator_sekitar }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jml Kabel DC Telkom</label>
                        <input type="number" name="jml_kabel_dc_telkom" class="form-control" min="0" value="{{ $tiang->jml_kabel_dc_telkom }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jml KU Telkom</label>
                        <input type="number" name="jml_ku_telkom" class="form-control" min="0" value="{{ $tiang->jml_ku_telkom }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Teknisi</label>
                        <input type="text" name="nama_teknisi" class="form-control" value="{{ $tiang->nama_teknisi }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ID Tiang Instansi</label>
                        <input type="text" name="id_tiang_instansi" class="form-control" value="{{ $tiang->id_tiang_instansi }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:70px">
            <div class="card-header py-3"><h6 class="mb-0"><i class="bi bi-wifi me-2"></i>ISP Penumpang</h6></div>
            <div class="card-body p-0" id="operators-container">
                @forelse($tiang->tiangOperator as $i => $pivot)
                <div class="op-row border-bottom p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <select name="operators[{{ $i }}][operator_id]" class="form-select form-select-sm" required>
                            @foreach($operators as $op)
                            <option value="{{ $op->id }}" {{ $pivot->operator_id == $op->id ? 'selected':'' }}>{{ $op->nama_operator }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2 btn-remove-op"><i class="bi bi-trash"></i></button>
                    </div>
                    <div class="row g-1 mb-2">
                        <div class="col-4"><input type="number" name="operators[{{ $i }}][jml_kabel_dc]" class="form-control form-control-sm" value="{{ $pivot->jml_kabel_dc }}"></div>
                        <div class="col-4"><input type="number" name="operators[{{ $i }}][jml_ku]" class="form-control form-control-sm" value="{{ $pivot->jml_ku }}"></div>
                        <div class="col-4"><input type="number" name="operators[{{ $i }}][jml_odp]" class="form-control form-control-sm" value="{{ $pivot->jml_odp }}"></div>
                    </div>
                    <select name="operators[{{ $i }}][status_legalitas]" class="form-select form-select-sm mb-2">
                        @foreach(['perlu_verifikasi','legal','ilegal'] as $s)
                        <option value="{{ $s }}" {{ $pivot->status_legalitas === $s ? 'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                    <textarea name="operators[{{ $i }}][keterangan]" class="form-control form-control-sm" rows="1">{{ $pivot->keterangan_operator }}</textarea>
                </div>
                @empty
                <div class="text-center text-muted py-3 small" id="op-empty">Belum ada ISP</div>
                @endforelse
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-sm btn-outline-primary w-100 mb-2" id="btnAddOp"><i class="bi bi-plus me-1"></i>Tambah ISP</button>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                <a href="{{ route('tiang.show', $tiang) }}" class="btn btn-light w-100 mt-2">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>

<template id="op-template">
    <div class="op-row border-bottom p-3">
        <div class="d-flex justify-content-between mb-2">
            <select name="operators[__IDX__][operator_id]" class="form-select form-select-sm" required>
                <option value="">— Pilih Operator —</option>
                @foreach($operators as $op)<option value="{{ $op->id }}">{{ $op->nama_operator }}</option>@endforeach
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger ms-2 btn-remove-op"><i class="bi bi-trash"></i></button>
        </div>
        <div class="row g-1 mb-2">
            <div class="col-4"><input type="number" name="operators[__IDX__][jml_kabel_dc]" class="form-control form-control-sm" value="0"></div>
            <div class="col-4"><input type="number" name="operators[__IDX__][jml_ku]" class="form-control form-control-sm" value="0"></div>
            <div class="col-4"><input type="number" name="operators[__IDX__][jml_odp]" class="form-control form-control-sm" value="0"></div>
        </div>
        <select name="operators[__IDX__][status_legalitas]" class="form-select form-select-sm mb-2">
            <option value="perlu_verifikasi">Perlu Verifikasi</option>
            <option value="legal">Legal</option>
            <option value="ilegal">Ilegal</option>
        </select>
        <textarea name="operators[__IDX__][keterangan]" class="form-control form-control-sm" rows="1"></textarea>
    </div>
</template>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Cascade dropdown (sama seperti create, hanya dengan preload)
document.getElementById('sel_district').addEventListener('change', async function() {
    const areaEl = document.getElementById('sel_area');
    const stoEl  = document.getElementById('sel_sto');
    areaEl.innerHTML = '<option value="">— Pilih Area —</option>';
    stoEl.innerHTML  = '<option value="">— Pilih STO —</option>';
    if (!this.value) return;
    const res = await fetch(`/api/master/areas?district_id=${this.value}`).then(r => r.json());
    res.data?.forEach(a => areaEl.add(new Option(a.name, a.id)));
});

document.getElementById('sel_area').addEventListener('change', async function() {
    const stoEl = document.getElementById('sel_sto');
    stoEl.innerHTML = '<option value="">— Pilih STO —</option>';
    if (!this.value) return;
    const res = await fetch(`/api/master/stos?area_id=${this.value}`).then(r => r.json());
    res.data?.forEach(s => {
        const o = new Option(`${s.kode} — ${s.nama||''}`, s.id);
        if (s.id == {{ $tiang->sto_id }}) o.selected = true;
        stoEl.add(o);
    });
});

// Pick map
const pickMap = L.map('pick-map').setView([{{ $tiang->latitude }}, {{ $tiang->longitude }}], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(pickMap);
let pickMarker = L.marker([{{ $tiang->latitude }}, {{ $tiang->longitude }}]).addTo(pickMap);
pickMap.on('click', e => {
    document.querySelector('[name="latitude"]').value = e.latlng.lat.toFixed(7);
    document.querySelector('[name="longitude"]').value = e.latlng.lng.toFixed(7);
    pickMarker.setLatLng(e.latlng);
});

// ISP
let opIdx = {{ $tiang->tiangOperator->count() }};
document.getElementById('btnAddOp')?.addEventListener('click', () => {
    const tpl = document.getElementById('op-template').innerHTML.replace(/__IDX__/g, opIdx++);
    document.getElementById('op-empty')?.remove();
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('operators-container').appendChild(div.firstElementChild);
});

document.getElementById('operators-container').addEventListener('click', e => {
    if (e.target.closest('.btn-remove-op')) e.target.closest('.op-row').remove();
});
</script>
@endpush
