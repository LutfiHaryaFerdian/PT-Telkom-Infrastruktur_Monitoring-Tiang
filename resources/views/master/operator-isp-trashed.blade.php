@extends('layouts.app')
@section('title', 'Master Operator ISP Terhapus')
@section('breadcrumb')
<li class="breadcrumb-item">Master</li>
<li class="breadcrumb-item"><a href="{{ route('master.operator-isp.index') }}">Operator ISP</a></li>
<li class="breadcrumb-item active">Terhapus</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Operator ISP Terhapus (Soft Delete)</h1>
    <a href="{{ route('master.operator-isp.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th>Nama Operator</th>
                        <th>Status</th>
                        <th>Dihapus Pada</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operators as $index => $o)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold text-danger">{{ $o->nama_operator }}</td>
                        <td>
                            @if($o->is_predefined)
                                <span class="badge bg-success">Predefined</span>
                            @else
                                <span class="badge bg-secondary">User Added</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $o->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('master.operator-isp.restore', $o->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:.78rem">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Pulihkan
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Tidak ada data operator ISP terhapus</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
