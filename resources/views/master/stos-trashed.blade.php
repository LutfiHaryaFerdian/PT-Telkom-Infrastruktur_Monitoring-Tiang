@extends('layouts.app')
@section('title', 'Master STO Terhapus')
@section('breadcrumb')
<li class="breadcrumb-item">Master</li>
<li class="breadcrumb-item"><a href="{{ route('master.stos.index') }}">STO</a></li>
<li class="breadcrumb-item active">Terhapus</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">STO Terhapus (Soft Delete)</h1>
    <a href="{{ route('master.stos.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.875rem">
                <thead>
                    <tr>
                        <th style="width: 50px">No</th>
                        <th>Kode</th>
                        <th>Nama STO</th>
                        <th>Area / District</th>
                        <th>Dihapus Pada</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stos as $index => $s)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-danger">{{ $s->kode }}</span></td>
                        <td class="fw-semibold">{{ $s->nama ?? '—' }}</td>
                        <td>
                            <span class="text-dark">{{ $s->area?->name ?? '—' }}</span>
                            <span class="text-muted small d-block">District: {{ $s->area?->district?->name ?? '—' }}</span>
                        </td>
                        <td class="text-muted">{{ $s->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('master.stos.restore', $s->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:.78rem">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Pulihkan
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada data STO terhapus</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
