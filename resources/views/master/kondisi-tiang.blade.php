@extends('layouts.app')
@section('title', 'Master Kondisi Tiang')
@section('breadcrumb')
<li class="breadcrumb-item">Master</li>
<li class="breadcrumb-item active">Kondisi Tiang</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Master Data Kondisi Tiang</h1>
        <p class="page-breadcrumb mb-0">Kelola level keparahan kondisi fisik tiang</p>
    </div>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kondisi
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.875rem">
                <thead>
                    <tr>
                        <th style="width: 50px">No</th>
                        <th>Nama Kondisi</th>
                        <th>Level Keparahan</th>
                        <th>Jumlah Tiang</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kondisiTiang as $index => $k)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $k->nama }}</td>
                        <td>
                            @php
                                $badgeColor = [
                                    'baik' => 'badge-baik',
                                    'perlu_perhatian' => 'badge-perlu',
                                    'rusak' => 'badge-rusak'
                                ][$k->level] ?? 'bg-secondary';
                            @endphp
                            <span class="badge-status {{ $badgeColor }}">{{ ucfirst(str_replace('_', ' ', $k->level)) }}</span>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $k->tiang_count }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" style="padding:.2rem .4rem;font-size:.78rem"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit{{ $k->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('master.kondisi-tiang.destroy', $k) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus kondisi {{ $k->nama }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:.2rem .4rem;font-size:.78rem">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit{{ $k->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Kondisi Tiang</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('master.kondisi-tiang.update', $k) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama Kondisi <span class="text-danger">*</span></label>
                                            <input type="text" name="nama" class="form-control" value="{{ $k->nama }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Level Keparahan <span class="text-danger">*</span></label>
                                            <select name="level" class="form-select" required>
                                                <option value="baik" {{ $k->level === 'baik' ? 'selected' : '' }}>Baik (Normal/OK)</option>
                                                <option value="perlu_perhatian" {{ $k->level === 'perlu_perhatian' ? 'selected' : '' }}>Perlu Perhatian (Anomali Ringan)</option>
                                                <option value="rusak" {{ $k->level === 'rusak' ? 'selected' : '' }}>Rusak / Roboh (Anomali Berat)</option>
                                            </select>
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
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data kondisi tiang</td>
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
                <h5 class="modal-title">Tambah Kondisi Tiang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.kondisi-tiang.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kondisi <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Keropos Bawah" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Level Keparahan <span class="text-danger">*</span></label>
                        <select name="level" class="form-select" required>
                            <option value="baik">Baik (Normal/OK)</option>
                            <option value="perlu_perhatian">Perlu Perhatian (Anomali Ringan)</option>
                            <option value="rusak">Rusak / Roboh (Anomali Berat)</option>
                        </select>
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
