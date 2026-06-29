@extends('layouts.app')
@section('title', 'Export Data')
@section('breadcrumb')
<li class="breadcrumb-item active">Export Data</li>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Export Data Tiang</h1>
    <p class="page-breadcrumb mb-0">Generate dan download laporan data tiang telekomunikasi</p>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-download me-2"></i>Parameter Laporan</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('export.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">District</label>
                            <select class="form-select" id="exp_district" name="district_id">
                                <option value="">Semua District</option>
                                @foreach($districts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">STO</label>
                            <select class="form-select" id="exp_sto" name="sto_id">
                                <option value="">Semua STO</option>
                                @foreach($stos as $s)
                                <option value="{{ $s->id }}">{{ $s->kode }} — {{ $s->nama ?? '—' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kondisi</label>
                            <select class="form-select" name="kondisi">
                                <option value="">Semua Kondisi</option>
                                @foreach($kondisiTiang as $k)
                                <option value="{{ $k->level }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Format Output <span class="text-danger">*</span></label>
                            <select class="form-select" name="format" required>
                                <option value="xlsx">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf — Maks 1000 baris)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dari Tanggal</label>
                            <input type="date" class="form-control" name="date_from">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="date_to">
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-file-earmark-arrow-down me-2"></i>Generate & Antrikan File
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Catatan Penggunaan</h6>
            </div>
            <div class="card-body" style="font-size:.875rem">
                <div class="alert alert-info py-2 mb-3">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Antrian Export (Queue):</strong> File export di-generate di background job. Silakan periksa folder <code>storage/app/public/exports/{user_id}/</code> secara berkala.
                </div>
                <h6 class="fw-semibold">Ketentuan Batasan:</h6>
                <ul>
                    <li>Format <strong>PDF</strong> dibatasi maksimal <strong>1.000 baris</strong> karena keterbatasan memory server untuk render layout PDF. Jika data Anda melebihi jumlah tersebut, silakan gunakan filter tanggal/wilayah atau pilih format <strong>Excel / CSV</strong>.</li>
                    <li>File export yang dihasilkan hanya disimpan di server selama <strong>24 jam</strong>. Setelah itu, scheduler pembersihan otomatis (<code>cleanup:exports</code>) akan menghapusnya secara permanen untuk menghemat kapasitas disk storage.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Filter cascade pada halaman export
document.getElementById('exp_district').addEventListener('change', async function() {
    const stoEl = document.getElementById('exp_sto');
    stoEl.innerHTML = '<option value="">Semua STO</option>';
    if (!this.value) {
        // Reload all STOs
        const res = await fetch('/api/master/stos').then(r => r.json());
        res.data?.forEach(s => stoEl.add(new Option(`${s.kode} — ${s.nama||''}`, s.id)));
        return;
    }
    const areas = await fetch(`/api/master/areas?district_id=${this.value}`).then(r => r.json());
    for (const a of (areas.data || [])) {
        const stos = await fetch(`/api/master/stos?area_id=${a.id}`).then(r => r.json());
        stos.data?.forEach(s => stoEl.add(new Option(`${s.kode} — ${s.nama||''}`, s.id)));
    }
});
</script>
@endpush
