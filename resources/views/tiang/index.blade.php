@extends('layouts.app')
@section('title','Data Tiang')
@section('breadcrumb')
<li class="breadcrumb-item active">Data Tiang</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Data Tiang Telekomunikasi</h1>
        <p class="page-breadcrumb mb-0">Kelola seluruh data tiang infrastruktur</p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->isAdmin())
        <a href="{{ route('tiang.trashed') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-trash me-1"></i>Terhapus
        </a>
        @endif
        <a href="{{ route('tiang.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Tiang
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter_district">
                    <option value="">Semua District</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter_sto">
                    <option value="">Semua STO</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter_kondisi">
                    <option value="">Semua Kondisi</option>
                    <option value="baik">Baik</option>
                    <option value="perlu_perhatian">Perlu Perhatian</option>
                    <option value="rusak">Rusak</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter_status">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="ok">OK</option>
                    <option value="ditolak">Ditolak</option>
                    <option value="double_input">Double Input</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter_anomali">
                    <option value="">Semua</option>
                    <option value="1">Ada Anomali</option>
                    <option value="0">Tidak Ada Anomali</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100" id="btnFilter">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="p-3 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small" id="tabel-info"></span>
                <input type="text" id="tabel-search" class="form-control form-control-sm" style="width:220px" placeholder="Cari kode / nama jalan...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tiangTable" style="font-size:.875rem">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Kode Tiang</th>
                        <th>STO</th>
                        <th>Nama Jalan</th>
                        <th>Kondisi</th>
                        <th>Verifikasi</th>
                        <th>Anomali</th>
                        <th>Tgl Input</th>
                        <th style="width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tiang-tbody">
                    <tr><td colspan="9" class="text-center py-4 text-muted">Memuat data...</td></tr>
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

async function loadTable() {
    const params = new URLSearchParams({
        draw: dtDraw++,
        start: dtStart,
        length: dtLength,
        'search[value]': dtSearch,
        ...currentFilters
    });

    const tbody = document.getElementById('tiang-tbody');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat...</td></tr>';

    const res = await fetch(`/tiang/data?${params}`);
    const json = await res.json();
    const rows = json.data || [];

    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data ditemukan</td></tr>';
        updatePagination(0, 0);
        return;
    }

    const levelBadge = { baik:'badge-baik', perlu_perhatian:'badge-perlu', rusak:'badge-rusak' };
    const statusBadge = { ok:'badge-ok', pending:'badge-pending', ditolak:'badge-ditolak', double_input:'badge-double' };

    tbody.innerHTML = rows.map((t, i) => `
        <tr>
            <td class="text-muted">${dtStart + i + 1}</td>
            <td><a href="/tiang/${t.id}" class="fw-semibold text-decoration-none text-primary">${t.kode_tiang||'—'}</a></td>
            <td><span class="badge bg-light text-dark">${t.sto?.kode||'—'}</span></td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${t.nama_jalan||'—'}</td>
            <td><span class="badge-status ${levelBadge[t.kondisi_tiang?.level]||''}">${t.kondisi_tiang?.nama||'—'}</span></td>
            <td><span class="badge-status ${statusBadge[t.status_verifikasi]||''}">${t.status_verifikasi||'—'}</span></td>
            <td>${t.has_anomali ? '<span class="badge-status badge-anomali"><i class="bi bi-exclamation-triangle-fill me-1"></i>Ya</span>' : '<span class="text-muted small">—</span>'}</td>
            <td class="text-muted">${t.tgl_input ? new Date(t.tgl_input).toLocaleDateString('id-ID') : '—'}</td>
            <td>
                <a href="/tiang/${t.id}" class="btn btn-xs btn-sm" style="padding:.2rem .5rem;font-size:.75rem;background:#e8f0fb;color:#1a3a5c" title="Detail"><i class="bi bi-eye"></i></a>
                <a href="/tiang/${t.id}/edit" class="btn btn-xs btn-sm" style="padding:.2rem .5rem;font-size:.75rem;background:#fef3c7;color:#92400e" title="Edit"><i class="bi bi-pencil"></i></a>
            </td>
        </tr>`).join('');

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
    ['filter_district','filter_sto','filter_kondisi','filter_status','filter_anomali'].forEach(id => {
        const v = document.getElementById(id).value;
        if (v) currentFilters[id] = v;
    });
    dtStart = 0; loadTable();
});

// Load districts dropdown
fetch('/api/master/districts').then(r => r.json()).then(json => {
    const sel = document.getElementById('filter_district');
    json.data?.forEach(d => sel.add(new Option(d.name, d.id)));
});

document.getElementById('filter_district').addEventListener('change', async function() {
    const sel = document.getElementById('filter_sto');
    sel.innerHTML = '<option value="">Semua STO</option>';
    if (!this.value) return;
    // Load areas first, then STOs
    const areas = await fetch(`/api/master/areas?district_id=${this.value}`).then(r => r.json());
    for (const a of (areas.data || [])) {
        const stos = await fetch(`/api/master/stos?area_id=${a.id}`).then(r => r.json());
        stos.data?.forEach(s => sel.add(new Option(`${s.kode} — ${s.nama||''}`, s.id)));
    }
});

loadTable();
</script>
@endpush
