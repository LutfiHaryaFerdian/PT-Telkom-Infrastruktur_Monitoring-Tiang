@extends('layouts.app')
@section('title', 'Tindak Lanjut ISP')
@section('breadcrumb')
<li class="breadcrumb-item active">Tindak Lanjut ISP</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Tindak Lanjut ISP Penumpang</h1>
        <p class="page-breadcrumb mb-0">Pantau dan kelola proses komunikasi serta penertiban kabel ISP liar</p>
    </div>
</div>

<!-- Ringkasan Kartu -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="stat-icon" style="background:#fee2e2;color:#dc3545">
                <i class="bi bi-envelope-x"></i>
            </div>
            <div>
                <div class="stat-value" id="stat-belum_disurati">{{ number_format($stats['belum_disurati'], 0, ',', '.') }}</div>
                <div class="stat-label text-muted">Belum Disurati</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="stat-icon" style="background:#fff3e8;color:#c05621">
                <i class="bi bi-telephone-forward"></i>
            </div>
            <div>
                <div class="stat-value" id="stat-perlu_followup">{{ number_format($stats['perlu_followup'], 0, ',', '.') }}</div>
                <div class="stat-label text-muted">Perlu Follow-up</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="stat-icon" style="background:#e8f0fb;color:#1e40af">
                <i class="bi bi-envelope"></i>
            </div>
            <div>
                <div class="stat-value" id="stat-menunggu_balasan">{{ number_format($stats['menunggu_balasan'], 0, ',', '.') }}</div>
                <div class="stat-label text-muted">Menunggu Balasan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="stat-icon" style="background:#d1fae5;color:#065f46">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <div class="stat-value" id="stat-selesai">{{ number_format($stats['selesai'], 0, ',', '.') }}</div>
                <div class="stat-label text-muted">Selesai</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">District</label>
                <select class="form-select form-select-sm" id="filter_district">
                    <option value="">Semua District</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Area</label>
                <select class="form-select form-select-sm" id="filter_area">
                    <option value="">Semua Area</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">STO</label>
                <select class="form-select form-select-sm" id="filter_sto">
                    <option value="">Semua STO</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Operator ISP</label>
                <select class="form-select form-select-sm" id="filter_operator">
                    <option value="">Semua Operator</option>
                    @foreach($operators as $op)
                        <option value="{{ $op->id }}">{{ $op->nama_operator }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status Tindak Lanjut</label>
                <select class="form-select form-select-sm" id="filter_status_tindaklanjut">
                    <option value="">Semua Status</option>
                    <option value="belum_disurati">Belum Disurati</option>
                    <option value="sudah_disurati">Sudah Disurati</option>
                    <option value="ada_balasan">Ada Balasan</option>
                    <option value="perlu_followup">Perlu Follow-up</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100" id="btnFilter" style="height:31px">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="p-3 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small" id="tabel-info"></span>
                <input type="text" id="tabel-search" class="form-control form-control-sm" style="width:240px" placeholder="Cari kode tiang / nama jalan...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tindaklanjutTable" style="font-size:.875rem">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px" class="ps-3">#</th>
                        <th>Kode Tiang</th>
                        <th>STO</th>
                        <th>Nama Jalan</th>
                        <th>ISP Penumpang</th>
                        <th>Status Legalitas</th>
                        <th>Status Tindak Lanjut</th>
                        <th>Surat Terakhir</th>
                        <th style="width:120px" class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tindaklanjut-tbody">
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                            Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" style="font-size:.82rem">
            <span id="dt-info" class="text-muted"></span>
            <div id="dt-pagination" class="d-flex gap-1"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let dtStart = 0, dtLength = 10, dtDraw = 1;
let dtSearch = '', currentFilters = {};

// Load districts
fetch('/api/master/districts').then(r => r.json()).then(json => {
    const sel = document.getElementById('filter_district');
    json.data?.forEach(d => sel.add(new Option(d.name, d.id)));
});

// Cascade district -> area
document.getElementById('filter_district').addEventListener('change', async function() {
    const areaSel = document.getElementById('filter_area');
    areaSel.innerHTML = '<option value="">Semua Area</option>';
    const stoSel = document.getElementById('filter_sto');
    stoSel.innerHTML = '<option value="">Semua STO</option>';

    if (!this.value) return;

    const res = await fetch(`/api/master/areas?district_id=${this.value}`).then(r => r.json());
    res.data?.forEach(a => areaSel.add(new Option(a.name, a.id)));
});

// Cascade area -> sto
document.getElementById('filter_area').addEventListener('change', async function() {
    const stoSel = document.getElementById('filter_sto');
    stoSel.innerHTML = '<option value="">Semua STO</option>';

    if (!this.value) return;

    const res = await fetch(`/api/master/stos?area_id=${this.value}`).then(r => r.json());
    res.data?.forEach(s => stoSel.add(new Option(`${s.kode} - ${s.nama||''}`, s.id)));
});

async function loadTable() {
    const params = new URLSearchParams({
        draw: dtDraw++,
        start: dtStart,
        length: dtLength,
        'search[value]': dtSearch,
        ...currentFilters
    });

    const tbody = document.getElementById('tindaklanjut-tbody');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2 text-primary"></div>Memuat data...</td></tr>';

    const res = await fetch(`/tindaklanjut/data?${params}`);
    const json = await res.json();
    const rows = json.data || [];

    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data ditemukan</td></tr>';
        updatePagination(0, 0);
        return;
    }

    // Update Cards
    if (json.stats) {
        document.getElementById('stat-belum_disurati').textContent = (json.stats.belum_disurati || 0).toLocaleString('id-ID');
        document.getElementById('stat-perlu_followup').textContent = (json.stats.perlu_followup || 0).toLocaleString('id-ID');
        document.getElementById('stat-menunggu_balasan').textContent = (json.stats.menunggu_balasan || 0).toLocaleString('id-ID');
        document.getElementById('stat-selesai').textContent = (json.stats.selesai || 0).toLocaleString('id-ID');
    }

    const legalBadge = {
        legal: 'badge-ok',
        ilegal: 'badge-ditolak',
        perlu_verifikasi: 'badge-pending'
    };

    const statusClass = {
        belum_disurati: 'bg-danger text-white',
        sudah_disurati: 'bg-warning text-dark',
        ada_balasan: 'bg-primary text-white',
        perlu_followup: 'bg-orange text-white',
        selesai: 'bg-success text-white'
    };

    tbody.innerHTML = rows.map((r, i) => {
        // Status legalitas label format
        let legalLabel = r.status_legalitas;
        if (legalLabel === 'perlu_verifikasi') legalLabel = 'Perlu Verifikasi';
        else if (legalLabel === 'legal') legalLabel = 'Legal';
        else if (legalLabel === 'ilegal') legalLabel = 'Ilegal';

        const tindakLanjutBg = statusClass[r.status_tindaklanjut] || 'bg-secondary text-white';

        return `
        <tr>
            <td class="text-muted ps-3">${dtStart + i + 1}</td>
            <td><a href="${r.action_url}" class="fw-semibold text-decoration-none text-primary">${r.kode_tiang}</a></td>
            <td><span class="badge bg-light text-dark">${r.sto}</span></td>
            <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.nama_jalan}</td>
            <td><b>${r.nama_operator}</b></td>
            <td><span class="badge-status ${legalBadge[r.status_legalitas]||''}">${legalLabel}</span></td>
            <td><span class="badge rounded-pill ${tindakLanjutBg}" style="font-size:.78rem;padding:.3rem .6rem">${r.status_tindaklanjut_label}</span></td>
            <td class="text-muted">${r.surat_terakhir}</td>
            <td class="pe-3">
                <a href="${r.action_url}" class="btn btn-xs btn-sm" style="padding:.25rem .6rem;font-size:.75rem;background:#e8f0fb;color:#1a3a5c">
                    <i class="bi bi-search me-1"></i>Detail
                </a>
            </td>
        </tr>`;
    }).join('');

    updatePagination(json.recordsFiltered, json.recordsTotal);
    document.getElementById('dt-info').textContent = `Menampilkan ${dtStart+1}–${Math.min(dtStart+dtLength, json.recordsFiltered)} dari ${json.recordsFiltered} entri`;
}

function updatePagination(filtered, total) {
    const pages = Math.ceil(filtered / dtLength);
    const currentPage = Math.floor(dtStart / dtLength);
    const container = document.getElementById('dt-pagination');
    container.innerHTML = '';

    const btn = (label, page, disabled, active) => {
        const b = document.createElement('button');
        b.className = `btn btn-sm ${active ? 'btn-primary' : 'btn-outline-secondary'}`;
        b.style.cssText = 'padding:.2rem .5rem;font-size:.78rem;';
        b.innerHTML = label; b.disabled = disabled;
        if (!disabled && !active) b.onclick = () => { dtStart = page * dtLength; loadTable(); };
        return b;
    };

    container.appendChild(btn('‹', currentPage - 1, currentPage === 0, false));
    for (let i = Math.max(0, currentPage-2); i <= Math.min(pages-1, currentPage+2); i++) {
        container.appendChild(btn(i+1, i, false, i === currentPage));
    }
    container.appendChild(btn('›', currentPage + 1, currentPage >= pages-1, false));
}

// Events
let searchTimer;
document.getElementById('tabel-search').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { dtSearch = this.value; dtStart = 0; loadTable(); }, 400);
});

document.getElementById('btnFilter').addEventListener('click', () => {
    currentFilters = {};
    ['filter_district', 'filter_area', 'filter_sto', 'filter_operator', 'filter_status_tindaklanjut'].forEach(id => {
        const v = document.getElementById(id).value;
        if (v) currentFilters[id] = v;
    });
    dtStart = 0; loadTable();
});

loadTable();
</script>
<style>
/* Custom style for bg-orange */
.bg-orange {
    background-color: #d97706 !important;
}
</style>
@endpush
