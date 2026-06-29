@extends('layouts.app')
@section('title', 'Master Jenis Tiang')
@section('breadcrumb')
<li class="breadcrumb-item">Master</li>
<li class="breadcrumb-item active">Jenis Tiang</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Master Data Jenis Tiang</h1>
        <p class="page-breadcrumb mb-0">Kelola spesifikasi jenis dan tinggi tiang</p>
    </div>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-plus-lg me-1"></i>Tambah Jenis Tiang
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.875rem">
                <thead>
                    <tr>
                        <th style="width: 50px">No</th>
                        <th>Nama Jenis</th>
                        <th>Tinggi (Meter)</th>
                        <th>Jumlah Tiang</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jenisTiang as $index => $j)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $j->nama }}</td>
                        <td>{{ $j->tinggi_m }} m</td>
                        <td><span class="badge bg-light text-dark">{{ $j->tiang_count }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" style="padding:.2rem .4rem;font-size:.78rem"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit{{ $j->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('master.jenis-tiang.destroy', $j) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus jenis tiang {{ $j->nama }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:.2rem .4rem;font-size:.78rem">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit{{ $j->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Jenis Tiang</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('master.jenis-tiang.update', $j) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama Jenis <span class="text-danger">*</span></label>
                                            <input type="text" name="nama" class="form-control" value="{{ $j->nama }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Tinggi (Meter) <span class="text-danger">*</span></label>
                                            <input type="number" name="tinggi_m" step="0.1" class="form-control" value="{{ $j->tinggi_m }}" required>
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
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data jenis tiang</td>
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
                <h5 class="modal-title">Tambah Jenis Tiang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.jenis-tiang.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Jenis <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: T-7-2 Segment" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tinggi (Meter) <span class="text-danger">*</span></label>
                        <input type="number" name="tinggi_m" step="0.1" class="form-control" placeholder="Contoh: 7.0" required>
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
