@extends('layouts.app')
@section('title', 'Master Area')
@section('breadcrumb')
<li class="breadcrumb-item">Master</li>
<li class="breadcrumb-item active">Area</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Master Data Area</h1>
        <p class="page-breadcrumb mb-0">Kelola sub-regional area wilayah kerja</p>
    </div>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-plus-lg me-1"></i>Tambah Area
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.875rem">
                <thead>
                    <tr>
                        <th style="width: 50px">No</th>
                        <th>District</th>
                        <th>Nama Area</th>
                        <th>Jumlah STO</th>
                        <th>Jumlah Tiang</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($areas as $index => $a)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-secondary">{{ $a->district?->name ?? '—' }}</span></td>
                        <td class="fw-semibold">{{ $a->name }}</td>
                        <td><span class="badge bg-light text-dark">{{ $a->stos_count }}</span></td>
                        <td><span class="badge bg-light text-dark">{{ $a->tiang_count }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" style="padding:.2rem .4rem;font-size:.78rem"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit{{ $a->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('master.areas.destroy', $a) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus area {{ $a->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:.2rem .4rem;font-size:.78rem">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit{{ $a->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Area</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('master.areas.update', $a) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">District <span class="text-danger">*</span></label>
                                            <select name="district_id" class="form-select" required>
                                                @foreach($districts as $d)
                                                <option value="{{ $d->id }}" {{ $a->district_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama Area <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ $a->name }}" required>
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
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data area</td>
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
                <h5 class="modal-title">Tambah Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.areas.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">District <span class="text-danger">*</span></label>
                        <select name="district_id" class="form-select" required>
                            <option value="">— Pilih District —</option>
                            @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Area <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Area Metro" required>
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
