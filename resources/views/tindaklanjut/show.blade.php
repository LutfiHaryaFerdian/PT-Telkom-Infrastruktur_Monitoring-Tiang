@extends('layouts.app')
@section('title', 'Detail Tindak Lanjut')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tindaklanjut.index') }}">Tindak Lanjut ISP</a></li>
<li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Detail Tindak Lanjut ISP</h1>
        <p class="page-breadcrumb mb-0">Kelola riwayat komunikasi dan tindak lanjut tiang telekomunikasi</p>
    </div>
    <a href="{{ route('tindaklanjut.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row g-4">
    <!-- Left Column: Info & Form -->
    <div class="col-lg-5">
        <!-- Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header py-3 bg-white border-bottom">
                <h6 class="mb-0 fw-semibold text-primary"><i class="bi bi-info-circle me-2"></i>Informasi Tiang & ISP</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-3" style="font-size: .9rem;">
                    <tr>
                        <td class="text-muted fw-medium" style="width: 140px;">Kode Tiang</td>
                        <td class="fw-semibold">: <a href="{{ route('tiang.show', $tiangOperator->tiang_id) }}" class="text-decoration-none">{{ $tiangOperator->tiang?->kode_tiang }}</a></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-medium">STO</td>
                        <td>: <span class="badge bg-light text-dark">{{ $tiangOperator->tiang?->sto?->kode }} - {{ $tiangOperator->tiang?->sto?->nama }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-medium">Nama Jalan</td>
                        <td>: {{ $tiangOperator->tiang?->nama_jalan }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-muted fw-medium pt-2">Operator ISP</td>
                        <td class="fw-bold pt-2">: {{ $tiangOperator->operator?->nama_operator }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-medium">Status Legalitas</td>
                        <td>: 
                            @php
                                $legalBadge = [
                                    'legal' => 'badge-ok',
                                    'ilegal' => 'badge-ditolak',
                                    'perlu_verifikasi' => 'badge-pending'
                                ];
                                $legalLabel = [
                                    'legal' => 'Legal',
                                    'ilegal' => 'Ilegal',
                                    'perlu_verifikasi' => 'Perlu Verifikasi'
                                ];
                            @endphp
                            <span class="badge-status {{ $legalBadge[$tiangOperator->status_legalitas] ?? '' }}">
                                {{ $legalLabel[$tiangOperator->status_legalitas] ?? $tiangOperator->status_legalitas }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-medium">Status Tindak Lanjut</td>
                        <td>: 
                            @php
                                $statusClass = [
                                    'belum_disurati' => 'bg-danger text-white',
                                    'sudah_disurati' => 'bg-warning text-dark',
                                    'ada_balasan' => 'bg-primary text-white',
                                    'perlu_followup' => 'bg-orange text-white',
                                    'selesai' => 'bg-success text-white'
                                ];
                            @endphp
                            <span id="badge-status-tindaklanjut" class="badge rounded-pill {{ $statusClass[$tiangOperator->status_tindaklanjut] ?? 'bg-secondary' }}" style="font-size: .82rem; padding: .35rem .7rem;">
                                {{ $tiangOperator->status_tindaklanjut_label }}
                            </span>
                        </td>
                    </tr>
                </table>

                @if(auth()->user()->isAdmin())
                    <div class="d-flex gap-2 border-top pt-3">
                        <button class="btn btn-sm btn-success flex-grow-1" id="btnSelesai" {{ $tiangOperator->status_tindaklanjut === 'selesai' ? 'disabled' : '' }}>
                            <i class="bi bi-check2-circle me-1"></i>Tandai Selesai
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="btnReset">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Status
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Form Tabs -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-0 border-bottom">
                <ul class="nav nav-tabs nav-fill" id="formTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3 small fw-semibold" id="surat-tab" data-bs-toggle="tab" data-bs-target="#suratPanel" type="button" role="tab">
                            <i class="bi bi-envelope-paper me-1"></i>Kirim Surat Baru
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 small fw-semibold" id="followup-tab" data-bs-toggle="tab" data-bs-target="#followupPanel" type="button" role="tab">
                            <i class="bi bi-telephone-forward me-1"></i>Catat Follow-up
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="formTabsContent">
                    <!-- Tab 1: Kirim Surat Baru -->
                    <div class="tab-pane fade show active" id="suratPanel" role="tabpanel">
                        <form id="formSurat" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tiang_operator_id" value="{{ $tiangOperator->id }}">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Nomor Surat (Opsional)</label>
                                <input type="text" name="nomor_surat" class="form-control form-control-sm" placeholder="Contoh: 123/TELKOM/2026">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Jenis Surat</label>
                                <select name="jenis_surat" class="form-select form-select-sm" required>
                                    <option value="pemberitahuan">Pemberitahuan</option>
                                    <option value="peringatan">Peringatan</option>
                                    <option value="konfirmasi">Konfirmasi</option>
                                    <option value="tagihan">Tagihan</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Tanggal Surat</label>
                                <input type="date" name="tanggal_surat" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Perihal</label>
                                <input type="text" name="perihal" class="form-control form-control-sm" placeholder="Contoh: Pemberitahuan Penertiban Kabel Liar" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Isi Ringkasan</label>
                                <textarea name="isi_ringkasan" class="form-control form-control-sm" rows="3" placeholder="Tuliskan ringkasan isi surat..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Unggah File Surat (PDF, Maks 10MB)</label>
                                <input type="file" name="file_surat" class="form-control form-control-sm" accept="application/pdf">
                            </div>

                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-save me-1"></i>Simpan Surat
                            </button>
                        </form>
                    </div>

                    <!-- Tab 2: Catat Follow-up -->
                    <div class="tab-pane fade" id="followupPanel" role="tabpanel">
                        <form id="formFollowup" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tiang_operator_id" value="{{ $tiangOperator->id }}">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Tanggal Follow-up</label>
                                <input type="date" name="tanggal_followup" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Metode</label>
                                <select name="metode" class="form-select form-select-sm" required>
                                    <option value="telepon">Telepon</option>
                                    <option value="email">Email</option>
                                    <option value="kunjungan_langsung">Kunjungan Langsung</option>
                                    <option value="rapat">Rapat</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Catatan</label>
                                <textarea name="catatan" class="form-control form-control-sm" rows="3" placeholder="Tuliskan isi pembicaraan atau tindakan..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Hasil</label>
                                <select name="hasil" class="form-select form-select-sm" required>
                                    <option value="berhasil_dihubungi">Berhasil Dihubungi</option>
                                    <option value="tidak_ada_respons">Tidak Ada Respons</option>
                                    <option value="dijadwalkan_ulang">Dijadwalkan Ulang</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Unggah Bukti (Gambar/PDF, Maks 10MB)</label>
                                <input type="file" name="file_bukti" class="form-control form-control-sm" accept="image/*,application/pdf">
                            </div>

                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-save me-1"></i>Simpan Follow-up
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Timeline -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-primary"><i class="bi bi-clock-history me-2"></i>Timeline Riwayat</h6>
                <button class="btn btn-xs btn-outline-secondary" onclick="reloadTimeline()" style="padding: .2rem .5rem; font-size: .75rem;">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            <div class="card-body" id="timelineContainer">
                @include('tindaklanjut.partials.timeline')
            </div>
        </div>
    </div>
</div>

<!-- Modal Balasan -->
<div class="modal fade" id="modalBalasan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title fw-semibold"><i class="bi bi-reply-all me-2"></i>Catat Balasan Surat</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBalasan" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="isp_surat_id" id="balasan_surat_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Terkait Surat</label>
                        <input type="text" id="balasan_surat_perihal" class="form-control form-control-sm bg-light" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Tanggal Balasan</label>
                        <input type="date" name="tanggal_balasan" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Status Balasan</label>
                        <select name="status_balasan" class="form-select form-select-sm" required>
                            <option value="positif">Positif (Menyetujui / Mengurus Izin)</option>
                            <option value="negatif">Negatif (Menolak / Tidak Mengaku)</option>
                            <option value="netral">Netral (Merespons tapi Belum Ada Keputusan)</option>
                            <option value="perlu_tindaklanjut">Perlu Tindak Lanjut Lebih Lanjut</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Ringkasan Balasan</label>
                        <textarea name="isi_ringkasan" class="form-control form-control-sm" rows="3" placeholder="Tuliskan isi balasan dari ISP..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Unggah File Balasan (PDF, Maks 10MB)</label>
                        <input type="file" name="file_balasan" class="form-control form-control-sm" accept="application/pdf">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-save me-1"></i>Simpan Balasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const tiangOperatorId = "{{ $tiangOperator->id }}";
const statusClass = {
    belum_disurati: 'bg-danger text-white',
    sudah_disurati: 'bg-warning text-dark',
    ada_balasan: 'bg-primary text-white',
    perlu_followup: 'bg-orange text-white',
    selesai: 'bg-success text-white'
};
const statusLabel = {
    belum_disurati: 'Belum Disurati',
    sudah_disurati: 'Sudah Disurati',
    ada_balasan: 'Ada Balasan',
    perlu_followup: 'Perlu Follow-up',
    selesai: 'Selesai'
};

async function reloadTimeline() {
    const container = document.getElementById('timelineContainer');
    container.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat ulang timeline...</div>';
    
    const res = await fetch(`/tindaklanjut/${tiangOperatorId}/timeline`);
    container.innerHTML = await res.text();
}

function updateStatusBadge(newStatus) {
    const badge = document.getElementById('badge-status-tindaklanjut');
    if (badge) {
        badge.className = `badge rounded-pill ${statusClass[newStatus] || 'bg-secondary'}`;
        badge.textContent = statusLabel[newStatus] || newStatus;
    }
    const btnSelesai = document.getElementById('btnSelesai');
    if (btnSelesai) {
        if (newStatus === 'selesai') {
            btnSelesai.disabled = true;
        } else {
            btnSelesai.disabled = false;
        }
    }
}

// Submit Surat
document.getElementById('formSurat').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Menyimpan...';

    const formData = new FormData(this);
    try {
        const res = await fetch("{{ route('isp-surat.store') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (json.success) {
            this.reset();
            this.querySelector('input[name="tanggal_surat"]').value = "{{ date('Y-m-d') }}";
            await reloadTimeline();
            const statusRes = await fetch(`/api/tiang/{{ $tiangOperator->tiang_id }}/isp-status`);
            const statusJson = await statusRes.json();
            const currentIsp = statusJson.data.isp_list.find(x => x.operator_id == "{{ $tiangOperator->operator_id }}");
            if (currentIsp) {
                updateStatusBadge(currentIsp.status_tindaklanjut);
            }
        } else {
            alert(json.message || 'Gagal menyimpan surat');
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan koneksi.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Surat';
    }
});

// Submit Followup
document.getElementById('formFollowup').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Menyimpan...';

    const formData = new FormData(this);
    try {
        const res = await fetch("{{ route('isp-followup.store') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (json.success) {
            this.reset();
            this.querySelector('input[name="tanggal_followup"]').value = "{{ date('Y-m-d') }}";
            await reloadTimeline();
            const statusRes = await fetch(`/api/tiang/{{ $tiangOperator->tiang_id }}/isp-status`);
            const statusJson = await statusRes.json();
            const currentIsp = statusJson.data.isp_list.find(x => x.operator_id == "{{ $tiangOperator->operator_id }}");
            if (currentIsp) {
                updateStatusBadge(currentIsp.status_tindaklanjut);
            }
        } else {
            alert(json.message || 'Gagal menyimpan follow-up');
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan koneksi.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Follow-up';
    }
});

// Modal Balasan triggers
function openBalasanModal(suratId, perihal) {
    document.getElementById('balasan_surat_id').value = suratId;
    document.getElementById('balasan_surat_perihal').value = perihal;
    const modal = new bootstrap.Modal(document.getElementById('modalBalasan'));
    modal.show();
}

// Submit Balasan
document.getElementById('formBalasan').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Menyimpan...';

    const formData = new FormData(this);
    try {
        const res = await fetch("{{ route('isp-balasan.store') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (json.success) {
            this.reset();
            this.querySelector('input[name="tanggal_balasan"]').value = "{{ date('Y-m-d') }}";
            bootstrap.Modal.getInstance(document.getElementById('modalBalasan')).hide();
            await reloadTimeline();
            const statusRes = await fetch(`/api/tiang/{{ $tiangOperator->tiang_id }}/isp-status`);
            const statusJson = await statusRes.json();
            const currentIsp = statusJson.data.isp_list.find(x => x.operator_id == "{{ $tiangOperator->operator_id }}");
            if (currentIsp) {
                updateStatusBadge(currentIsp.status_tindaklanjut);
            }
        } else {
            alert(json.message || 'Gagal menyimpan balasan');
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan koneksi.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Balasan';
    }
});

// Delete Surat
async function deleteSurat(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus surat ini? Semua balasan terkait juga akan terhapus.')) return;
    try {
        const res = await fetch(`/isp-surat/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const json = await res.json();
        if (json.success) {
            await reloadTimeline();
            const statusRes = await fetch(`/api/tiang/{{ $tiangOperator->tiang_id }}/isp-status`);
            const statusJson = await statusRes.json();
            const currentIsp = statusJson.data.isp_list.find(x => x.operator_id == "{{ $tiangOperator->operator_id }}");
            if (currentIsp) {
                updateStatusBadge(currentIsp.status_tindaklanjut);
            }
        } else {
            alert(json.message);
        }
    } catch (err) {
        console.error(err);
    }
}

// Delete Balasan
async function deleteBalasan(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus balasan ini?')) return;
    try {
        const res = await fetch(`/isp-balasan/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const json = await res.json();
        if (json.success) {
            await reloadTimeline();
            const statusRes = await fetch(`/api/tiang/{{ $tiangOperator->tiang_id }}/isp-status`);
            const statusJson = await statusRes.json();
            const currentIsp = statusJson.data.isp_list.find(x => x.operator_id == "{{ $tiangOperator->operator_id }}");
            if (currentIsp) {
                updateStatusBadge(currentIsp.status_tindaklanjut);
            }
        } else {
            alert(json.message);
        }
    } catch (err) {
        console.error(err);
    }
}

// Delete Followup
async function deleteFollowup(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus followup ini?')) return;
    try {
        const res = await fetch(`/isp-followup/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const json = await res.json();
        if (json.success) {
            await reloadTimeline();
            const statusRes = await fetch(`/api/tiang/{{ $tiangOperator->tiang_id }}/isp-status`);
            const statusJson = await statusRes.json();
            const currentIsp = statusJson.data.isp_list.find(x => x.operator_id == "{{ $tiangOperator->operator_id }}");
            if (currentIsp) {
                updateStatusBadge(currentIsp.status_tindaklanjut);
            }
        } else {
            alert(json.message);
        }
    } catch (err) {
        console.error(err);
    }
}

// Admin: Mark Selesai
const btnSelesai = document.getElementById('btnSelesai');
if (btnSelesai) {
    btnSelesai.addEventListener('click', async function() {
        if (!confirm('Tandai proses tindak lanjut ini selesai? Status tidak akan ter-override otomatis lagi.')) return;
        btnSelesai.disabled = true;
        
        try {
            const res = await fetch("{{ route('tindaklanjut.selesai', $tiangOperator->id) }}", {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await res.json();
            if (json.success) {
                updateStatusBadge('selesai');
            } else {
                alert(json.message);
                btnSelesai.disabled = false;
            }
        } catch (err) {
            console.error(err);
            btnSelesai.disabled = false;
        }
    });
}

// Admin: Reset Status
const btnReset = document.getElementById('btnReset');
if (btnReset) {
    btnReset.addEventListener('click', async function() {
        if (!confirm('Reset status tindak lanjut? Status akan dikalkulasi ulang secara otomatis.')) return;
        
        try {
            const res = await fetch("{{ route('tindaklanjut.reset', $tiangOperator->id) }}", {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await res.json();
            if (json.success) {
                const statusRes = await fetch(`/api/tiang/{{ $tiangOperator->tiang_id }}/isp-status`);
                const statusJson = await statusRes.json();
                const currentIsp = statusJson.data.isp_list.find(x => x.operator_id == "{{ $tiangOperator->operator_id }}");
                if (currentIsp) {
                    updateStatusBadge(currentIsp.status_tindaklanjut);
                }
            } else {
                alert(json.message);
            }
        } catch (err) {
            console.error(err);
        }
    });
}
</script>
<style>
.bg-orange {
    background-color: #d97706 !important;
}
.btn-xs {
    padding: .15rem .4rem;
    font-size: .75rem;
    border-radius: .2rem;
}
</style>
@endpush
