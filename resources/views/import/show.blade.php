@extends('layouts.app')
@section('title', 'Status Import — ' . $history->id)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('import.index') }}">Import Excel</a></li>
<li class="breadcrumb-item active">Detail #{{ $history->id }}</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Status Import #{{ $history->id }}</h1>
        <p class="page-breadcrumb mb-0">File: <span class="fw-semibold">{{ $history->filename }}</span></p>
    </div>
    <a href="{{ route('import.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row g-3">
    <!-- Status Progress Card -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Status Proses</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status:</span>
                    @php
                        $stClass = [
                            'processing' => 'bg-warning text-dark',
                            'done'       => 'bg-success text-white',
                            'failed'     => 'bg-danger text-white'
                        ][$history->status] ?? 'bg-secondary';
                    @endphp
                    <span class="badge {{ $stClass }}" id="import-status">{{ ucfirst($history->status) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Baris:</span>
                    <span class="fw-semibold" id="total-rows">{{ $history->total_rows ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Berhasil:</span>
                    <span class="text-success fw-semibold" id="success-rows">{{ $history->success_rows }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Gagal:</span>
                    <span class="text-danger fw-semibold" id="failed-rows">{{ $history->failed_rows }}</span>
                </div>

                <div class="progress mb-2" style="height:10px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $history->status==='failed'?'bg-danger':($history->status==='done'?'bg-success':'bg-warning') }}"
                         id="progress-bar-el" role="progressbar" style="width: {{ $history->progress_percent }}%"></div>
                </div>
                <div class="text-center fw-semibold small" id="progress-text">{{ $history->progress_percent }}% Selesai</div>
            </div>
        </div>
    </div>

    <!-- Errors Log -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-danger"><i class="bi bi-x-circle me-2"></i>Log Kesalahan Baris ({{ $errorLogs->total() }})</h6>
            </div>
            <div class="card-body p-0">
                @if($errorLogs->isEmpty())
                    <div class="text-center text-muted py-5 small" id="no-errors-el">
                        <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                        Tidak ada baris yang gagal diimport.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:.85rem">
                            <thead><tr class="table-light"><th style="width:60px">Baris</th><th>Pesan Kesalahan</th><th>Data Baris</th></tr></thead>
                            <tbody id="error-tbody">
                                @foreach($errorLogs as $err)
                                <tr>
                                    <td class="fw-semibold text-center">{{ $err->row_number }}</td>
                                    <td class="text-danger">{{ $err->error_message }}</td>
                                    <td>
                                        <pre class="mb-0 p-1 bg-light rounded" style="font-size:.7rem;max-height:80px;overflow-y:auto">{{ json_encode($err->raw_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">{{ $errorLogs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($history->status === 'processing')
<script>
let pollInterval = setInterval(async () => {
    const res = await fetch('/api/import/{{ $history->id }}/progress');
    const json = await res.json();
    const data = json.data;

    if (!data) return;

    // Update stats
    document.getElementById('import-status').textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
    document.getElementById('import-status').className = 'badge ' + {
        'processing': 'bg-warning text-dark',
        'done': 'bg-success text-white',
        'failed': 'bg-danger text-white'
    }[data.status];

    document.getElementById('total-rows').textContent = data.total_rows || '—';
    document.getElementById('success-rows').textContent = data.success_rows;
    document.getElementById('failed-rows').textContent = data.failed_rows;

    const bar = document.getElementById('progress-bar-el');
    bar.style.width = data.progress_percent + '%';
    if (data.status === 'done') {
        bar.className = 'progress-bar bg-success';
    } else if (data.status === 'failed') {
        bar.className = 'progress-bar bg-danger';
    }
    document.getElementById('progress-text').textContent = data.progress_percent + '% Selesai';

    if (data.status !== 'processing') {
        clearInterval(pollInterval);
        setTimeout(() => location.reload(), 1500); // Reload untuk render log error terbaru
    }
}, 2000);
</script>
@endif
@endpush
