@extends('layouts.app')
@section('title', 'Master STO')
@section('breadcrumb')
<li class="breadcrumb-item">Master</li>
<li class="breadcrumb-item active">STO</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Master Data STO (Sentral Telepon Otomat)</h1>
        <p class="page-breadcrumb mb-0">Kelola daftar sentral STO</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.stos.trashed') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-trash me-1"></i>Terhapus
        </a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
            <i class="bi bi-plus-lg me-1"></i>Tambah STO
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
                        <th>Kode</th>
                        <th>Nama STO</th>
                        <th>Area / District</th>
                        <th>Jumlah Tiang</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stos as $index => $s)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-primary">{{ $s->kode }}</span></td>
                        <td class="fw-semibold">{{ $s->nama ?? '—' }}</td>
                        <td>
                            <span class="text-dark">{{ $s->area?->name ?? '—' }}</span>
                            <span class="text-muted small d-block">District: {{ $s->area?->district?->name ?? '—' }}</span>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $s->tiang_count }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" style="padding:.2rem .4rem;font-size:.78rem"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit{{ $s->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('master.stos.destroy', $s) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus STO {{ $s->kode }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:.2rem .4rem;font-size:.78rem">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit{{ $s->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit STO</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('master.stos.update', $s) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Area <span class="text-danger">*</span></label>
                                            <select name="area_id" class="form-select" required>
                                                @foreach($areas as $a)
                                                <option value="{{ $a->id }}" {{ $s->area_id == $a->id ? 'selected' : '' }}>
                                                    {{ $a->name }} (District: {{ $a->district?->name ?? '—' }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Kode STO <span class="text-danger">*</span></label>
                                            <input type="text" name="kode" class="form-control" value="{{ $s->kode }}" required placeholder="Contoh: KDT">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama STO</label>
                                            <input type="text" name="nama" class="form-control" value="{{ $s->nama }}" placeholder="Contoh: Kedaton">
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
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data STO</td>
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
                <h5 class="modal-title">Tambah STO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.stos.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Area <span class="text-danger">*</span></label>
                        <select name="area_id" class="form-select" required>
                            <option value="">— Pilih Area —</option>
                            @foreach($areas as $a)
                            <option value="{{ $a->id }}">{{ $a->name }} (District: {{ $a->district?->name ?? '—' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode STO <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: TBU" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama STO</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Teluk Betung">
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
