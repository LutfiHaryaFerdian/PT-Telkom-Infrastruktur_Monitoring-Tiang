@extends('layouts.app')
@section('title','Data Tiang Terhapus')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tiang.index') }}">Data Tiang</a></li>
<li class="breadcrumb-item active">Terhapus</li>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Tiang Terhapus (Soft Delete)</h1>
    <a href="{{ route('tiang.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.875rem">
                <thead><tr>
                    <th>Kode Tiang</th><th>STO</th><th>Nama Jalan</th><th>Dihapus</th><th>Oleh</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($tiangTrashed as $t)
                <tr>
                    <td class="fw-semibold">{{ $t->kode_tiang ?? '—' }}</td>
                    <td>{{ $t->sto?->kode ?? '—' }}</td>
                    <td>{{ $t->nama_jalan }}</td>
                    <td class="text-muted">{{ $t->deleted_at?->format('d/m/Y H:i') }}</td>
                    <td class="text-muted">{{ $t->createdBy?->name ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('tiang.restore', $t->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:.75rem">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Pulihkan
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data terhapus</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $tiangTrashed->links() }}</div>
    </div>
</div>
@endsection
