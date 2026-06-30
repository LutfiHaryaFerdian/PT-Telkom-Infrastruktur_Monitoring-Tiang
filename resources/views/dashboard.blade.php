@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
<style>
#dashboard-map { height: 420px; border-radius: 10px; overflow: hidden; z-index: 1; }
.map-legend { background: rgba(255,255,255,.95); padding: .6rem .9rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.15); font-size: .78rem; }
.map-legend-item { display: flex; align-items: center; gap: .4rem; margin-bottom: .25rem; }
.legend-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
.search-on-map { position: absolute; top: 10px; left: 50%; transform: translateX(-50%); z-index: 999; width: 280px; }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-breadcrumb mb-0">Monitoring Infrastruktur Tiang — District Lampung</p>
    </div>
    <button class="btn btn-sm" style="background:#f0f2f5;color:#1a3a5c;" data-bs-toggle="collapse" data-bs-target="#filterPanel">
        <i class="bi bi-funnel me-1"></i>Filter
    </button>
</div>

<!-- Filter Panel -->
<div class="collapse {{ !empty($filter) ? 'show' : '' }} mb-3" id="filterPanel">
    <div class="card">
        <div class="card-body py-3">
            <form id="filterForm" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">District</label>
                    <select class="form-select form-select-sm" id="f_district" name="district_id">
                        <option value="">Semua</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Area</label>
                    <select class="form-select form-select-sm" id="f_area" name="area_id">
                        <option value="">Semua</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">STO</label>
                    <select class="form-select form-select-sm" id="f_sto" name="sto_id">
                        <option value="">Semua</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                    <input type="date" class="form-control form-control-sm" id="f_date_from" name="date_from" value="{{ $filter['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm" id="f_date_to" name="date_to" value="{{ $filter['date_to'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i>Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stat Cards Row 1 -->
<div class="row g-3 mb-3" id="stats-row-1">
    @foreach([
        ['id'=>'total_tiang','label'=>'Total Tiang','icon'=>'broadcast','color'=>'#1a3a5c','bg'=>'#e8f0fb'],
        ['id'=>'total_district','label'=>'District','icon'=>'geo-alt','color'=>'#6f42c1','bg'=>'#f0ebff'],
        ['id'=>'total_area','label'=>'Area','icon'=>'map','color'=>'#0d9488','bg'=>'#e6faf8'],
        ['id'=>'total_sto','label'=>'STO','icon'=>'building','color'=>'#c05621','bg'=>'#fff3e8'],
    ] as $s)
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="stat-icon" style="background:{{ $s['bg'] }};color:{{ $s['color'] }}">
                <i class="bi bi-{{ $s['icon'] }}"></i>
            </div>
            <div>
                <div class="stat-value" id="stat-{{ $s['id'] }}">—</div>
                <div class="stat-label">{{ $s['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Stat Cards Row 2 -->
<div class="row g-3 mb-4">
    @foreach([
        ['id'=>'tiang_kondisi_nok','label'=>'Kondisi NOK','icon'=>'exclamation-triangle','color'=>'#92400e','bg'=>'#fef3c7'],
        ['id'=>'anomali_aktif','label'=>'Anomali Aktif','icon'=>'bug','color'=>'#991b1b','bg'=>'#fee2e2'],
        ['id'=>'tiang_pending_verifikasi','label'=>'Menunggu Verifikasi','icon'=>'clock','color'=>'#1e40af','bg'=>'#dbeafe'],
        ['id'=>'total_operator','label'=>'Total Operator','icon'=>'wifi','color'=>'#065f46','bg'=>'#d1fae5'],
    ] as $s)
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="stat-icon" style="background:{{ $s['bg'] }};color:{{ $s['color'] }}">
                <i class="bi bi-{{ $s['icon'] }}"></i>
            </div>
            <div>
                <div class="stat-value" id="stat-{{ $s['id'] }}">—</div>
                <div class="stat-label">{{ $s['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Tiang per STO (Top 10)</h6>
            </div>
            <div class="card-body">
                <canvas id="chartSto" height="200"></canvas>
                <p class="text-center text-muted small mt-2 d-none" id="chartStoEmpty">Belum ada data</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Breakdown Kondisi</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="chartKondisi" height="200"></canvas>
                <p class="text-center text-muted small d-none" id="chartKondisiEmpty">Belum ada data</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-bar-chart-steps me-2"></i>Top 5 Operator</h6>
            </div>
            <div class="card-body">
                <canvas id="chartOperator" height="200"></canvas>
                <p class="text-center text-muted small d-none" id="chartOperatorEmpty">Belum ada data</p>
            </div>
        </div>
    </div>
</div>

<!-- Table + Map -->
<div class="row g-3">
    <!-- Anomali Terbaru -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-bug text-danger me-2"></i>5 Anomali Terbaru</h6>
                <span class="badge bg-danger" id="badge-anomali">0</span>
            </div>
            <div class="card-body p-0">
                <div id="anomali-list" class="list-group list-group-flush">
                    <div class="text-center text-muted py-4 small">Memuat data...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Peta Leaflet -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-map me-2"></i>Peta Sebaran Tiang</h6>
            </div>
            <div class="card-body p-2 position-relative">
                <!-- Search on map -->
                <div class="search-on-map">
                    <input type="text" id="mapSearch" class="form-control form-control-sm shadow" placeholder="🔍 Cari kode/nama jalan...">
                    <div id="mapSearchResult" class="bg-white shadow rounded mt-1" style="display:none;max-height:160px;overflow-y:auto;font-size:.82rem;"></div>
                </div>
                <div id="dashboard-map"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── STATE ──────────────────────────────────────────────────────
let currentFilter = @json($filter ?? []);
let chartSto, chartKondisi, chartOperator;

// ── PETA ──────────────────────────────────────────────────────
const map = L.map('dashboard-map').setView([-5.35, 105.25], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(map);

// Legend
const legend = L.control({ position: 'bottomright' });
legend.onAdd = () => {
    const div = L.DomUtil.create('div', 'map-legend');
    div.innerHTML = `
        <div class="map-legend-item"><div class="legend-dot" style="background:#dc3545"></div>Anomali</div>
        <div class="map-legend-item"><div class="legend-dot" style="background:#ffc107"></div>Pending Verifikasi</div>
        <div class="map-legend-item"><div class="legend-dot" style="background:#198754"></div>OK</div>`;
    return div;
};
legend.addTo(map);

const markerCluster = L.markerClusterGroup({ chunkedLoading: true });
map.addLayer(markerCluster);

function markerColor(d) {
    if (d.has_anomali) return '#dc3545';
    if (d.status_verifikasi === 'pending') return '#ffc107';
    return '#198754';
}

function makeIcon(color) {
    return L.divIcon({
        className: '',
        html: `<div style="width:12px;height:12px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>`,
        iconSize: [12, 12], iconAnchor: [6, 6]
    });
}

async function loadMarkers() {
    const params = new URLSearchParams({ ...currentFilter, per_page: 500 });
    const res = await fetch(`/api/tiang/map?${params}`);
    const json = await res.json();
    markerCluster.clearLayers();
    (json.data?.data || []).forEach(d => {
        const color = markerColor(d);
        const m = L.marker([d.latitude, d.longitude], { icon: makeIcon(color) });
        m.bindPopup(`<b>${d.kode_tiang || 'N/A'}</b><br><small>${d.status_verifikasi}</small><br><a href="/tiang/${d.id}" class="btn btn-xs btn-sm btn-primary mt-1" style="font-size:.75rem;padding:.2rem .5rem">Detail</a>`);
        markerCluster.addLayer(m);
    });
}

async function fitMapBounds() {
    const params = new URLSearchParams(currentFilter);
    const res = await fetch(`/api/tiang/map/bounds?${params}`);
    const json = await res.json();
    const b = json.data;
    if (b && b.lat_min) {
        map.fitBounds([[b.lat_min, b.lng_min],[b.lat_max, b.lng_max]], { padding: [30,30] });
    } else {
        map.setView([-5.35, 105.25], 10);
    }
}

// ── SEARCH ON MAP ──────────────────────────────────────────────
let searchTimer;
document.getElementById('mapSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (!q) { document.getElementById('mapSearchResult').style.display = 'none'; return; }
    searchTimer = setTimeout(async () => {
        const res = await fetch(`/api/search/tiang?q=${encodeURIComponent(q)}`);
        const json = await res.json();
        const box = document.getElementById('mapSearchResult');
        if (!json.data?.length) { box.style.display = 'none'; return; }
        box.style.display = 'block';
        box.innerHTML = json.data.map(t =>
            `<div class="p-2 border-bottom" style="cursor:pointer" onclick="flyTo(${t.latitude},${t.longitude},${t.id})">
                <b>${t.kode_tiang || 'N/A'}</b><br><small class="text-muted">${t.nama_jalan}</small>
            </div>`
        ).join('');
    }, 300);
});

function flyTo(lat, lng, id) {
    map.flyTo([lat, lng], 17);
    document.getElementById('mapSearch').value = '';
    document.getElementById('mapSearchResult').style.display = 'none';
}

// ── STATS ─────────────────────────────────────────────────────
async function loadStats() {
    const params = new URLSearchParams(currentFilter);
    const res = await fetch(`/api/dashboard/stats?${params}`);
    const json = await res.json();
    const s = json.data;

    ['total_tiang','total_district','total_area','total_sto','tiang_kondisi_nok',
     'anomali_aktif','tiang_pending_verifikasi','total_operator'].forEach(k => {
        const el = document.getElementById(`stat-${k}`);
        if (el) el.textContent = (s[k] ?? 0).toLocaleString('id-ID');
    });

    document.getElementById('badge-anomali').textContent = s.anomali_aktif ?? 0;

    // Chart STO
    const stoData = Array.isArray(s?.per_sto) ? s.per_sto : [];
    updateChart(chartSto, stoData.map(x => x.sto_kode), stoData.map(x => x.total), 'chartStoEmpty');

    // Chart Kondisi
    const kondisiData = Array.isArray(s?.per_kondisi) ? s.per_kondisi : [];
    const kondisiColors = { baik: '#198754', perlu_perhatian: '#ffc107', rusak: '#dc3545' };
    updateDonut(chartKondisi, kondisiData.map(x => x.kondisi_nama), kondisiData.map(x => x.total),
        kondisiData.map(x => kondisiColors[x.kondisi_level] || '#6c757d'), 'chartKondisiEmpty');

    // Chart Operator
    const opData = Array.isArray(s?.per_operator_top5) ? s.per_operator_top5 : [];
    updateHBar(chartOperator, opData.map(x => x.nama_operator), opData.map(x => x.total), 'chartOperatorEmpty');
}

// ── ANOMALI LIST ───────────────────────────────────────────────
async function loadAnomali() {
    const res = await fetch('/api/anomali/aktif');
    const json = await res.json();
    const list = (json.data || []).slice(0, 5);
    const container = document.getElementById('anomali-list');
    if (!list.length) {
        container.innerHTML = '<div class="text-center text-muted py-4 small"><i class="bi bi-check-circle text-success d-block fs-3 mb-2"></i>Tidak ada anomali aktif</div>';
        return;
    }
    const jenisLabel = { double_input:'Double Input', isp_tidak_teridentifikasi:'ISP Tidak Teridentifikasi',
        kondisi_nok:'Kondisi NOK', verifikasi_pending:'Pending Verifikasi',
        koordinat_tidak_valid:'Koordinat Tidak Valid', data_tidak_lengkap:'Data Tidak Lengkap' };

    container.innerHTML = list.map(a => `
        <a href="/tiang/${a.tiang_id}" class="list-group-item list-group-item-action py-2 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="badge bg-danger mb-1" style="font-size:.68rem">${jenisLabel[a.jenis]||a.jenis}</span>
                    <div class="fw-semibold" style="font-size:.83rem">${a.kode_tiang||'Tiang #'+a.tiang_id}</div>
                    <div class="text-muted" style="font-size:.77rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px">${a.keterangan}</div>
                </div>
            </div>
        </a>`).join('');
}

// ── CHARTS INIT ───────────────────────────────────────────────
function initCharts() {
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 11;

    chartSto = new Chart(document.getElementById('chartSto'), {
        type: 'bar',
        data: { labels: [], datasets: [{ data: [], backgroundColor: '#1a3a5c', borderRadius: 4, label: 'Jumlah Tiang' }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    chartKondisi = new Chart(document.getElementById('chartKondisi'), {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [], backgroundColor: [] }] },
        options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8 } } } }
    });

    chartOperator = new Chart(document.getElementById('chartOperator'), {
        type: 'bar',
        data: { labels: [], datasets: [{ data: [], backgroundColor: '#0d9488', borderRadius: 4, label: 'Tiang' }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
}

function updateChart(chart, labels, data, emptyId) {
    const isEmpty = !data.length || data.every(v => v === 0);
    document.getElementById(emptyId)?.classList.toggle('d-none', !isEmpty);
    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    chart.update();
}

function updateDonut(chart, labels, data, colors, emptyId) {
    const isEmpty = !data.length || data.every(v => v === 0);
    document.getElementById(emptyId)?.classList.toggle('d-none', !isEmpty);
    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    chart.data.datasets[0].backgroundColor = colors;
    chart.update();
}

function updateHBar(chart, labels, data, emptyId) {
    updateChart(chart, labels, data, emptyId);
}

// ── FILTER FORM ───────────────────────────────────────────────
async function loadDropdowns() {
    const districts = await fetch('/api/master/districts').then(r => r.json());
    const sel = document.getElementById('f_district');
    districts.data?.forEach(d => {
        const opt = new Option(d.name, d.id);
        if (currentFilter.district_id == d.id) opt.selected = true;
        sel.add(opt);
    });
    if (currentFilter.district_id) loadAreas(currentFilter.district_id);
    if (currentFilter.area_id) loadStos(currentFilter.area_id);
}

async function loadAreas(districtId) {
    const sel = document.getElementById('f_area');
    sel.innerHTML = '<option value="">Semua</option>';
    if (!districtId) return;
    const res = await fetch(`/api/master/areas?district_id=${districtId}`).then(r => r.json());
    res.data?.forEach(a => {
        const opt = new Option(a.name, a.id);
        if (currentFilter.area_id == a.id) opt.selected = true;
        sel.add(opt);
    });
}

async function loadStos(areaId) {
    const sel = document.getElementById('f_sto');
    sel.innerHTML = '<option value="">Semua</option>';
    if (!areaId) return;
    const res = await fetch(`/api/master/stos?area_id=${areaId}`).then(r => r.json());
    res.data?.forEach(s => {
        const opt = new Option(`${s.kode} - ${s.nama||''}`, s.id);
        if (currentFilter.sto_id == s.id) opt.selected = true;
        sel.add(opt);
    });
}

document.getElementById('f_district').addEventListener('change', function() { loadAreas(this.value); });
document.getElementById('f_area').addEventListener('change', function() { loadStos(this.value); });

document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    currentFilter = {};
    fd.forEach((v, k) => { if (v) currentFilter[k] = v; });
    // Simpan ke session via redirect
    const params = new URLSearchParams(currentFilter);
    window.location.href = `/dashboard?${params}`;
});

// ── INIT ──────────────────────────────────────────────────────
initCharts();
loadDropdowns();
Promise.all([loadStats(), loadMarkers(), loadAnomali(), fitMapBounds()]);
</script>
@endpush
