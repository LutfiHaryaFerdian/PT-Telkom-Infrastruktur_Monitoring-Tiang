@extends('layouts.app')
@section('title', 'Master Operator ISP')
@section('breadcrumb')
<li class="breadcrumb-item">Master</li>
<li class="breadcrumb-item active">Operator ISP</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Master Data Operator ISP</h1>
        <p class="page-breadcrumb mb-0">Kelola daftar ISP kompetitor/penumpang kabel tiang</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.operator-isp.trashed') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-trash me-1"></i>Terhapus
        </a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
            <i class="bi bi-plus-lg me-1"></i>Tambah Operator
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.875rem">
                <thead>
                    <tr>
                        <th style="width: 50px">No</th>
                        <th>Nama Operator</th>
                        <th>Predefined</th>
                        <th>Jumlah Tiang</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operators as $index => $o)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold text-primary">{{ $o->nama_operator }}</td>
                        <td>
                            @if($o->is_predefined)
                                <span class="badge bg-success">Predefined</span>
                            @else
                                <span class="badge bg-secondary">User Added</span>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $o->tiang_count }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" style="padding:.2rem .4rem;font-size:.78rem"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit{{ $o->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('master.operator-isp.destroy', $o) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus operator {{ $o->nama_operator }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:.2rem .4rem;font-size:.78rem">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit{{ $o->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Operator ISP</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('master.operator-isp.update', $o) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama Operator <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_operator" class="form-control" value="{{ $o->nama_operator }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data operator ISP</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Operator ISP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.operator-isp.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Operator <span class="text-danger">*</span></label>
                        <input type="text" name="nama_operator" class="form-control" placeholder="Contoh: Biznet" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_predefined" value="1" id="is_predefined">
                        <label class="form-check-label form-label mb-0" for="is_predefined">
                            Jadikan Predefined Operator
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
