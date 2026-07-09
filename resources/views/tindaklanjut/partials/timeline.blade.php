@php
    $items = collect();
    foreach ($tiangOperator->ispSurat as $surat) {
        $items->push((object)[
            'id' => $surat->id,
            'type' => 'surat',
            'date' => $surat->tanggal_surat,
            'model' => $surat,
        ]);
    }
    foreach ($tiangOperator->ispFollowup as $fu) {
        $items->push((object)[
            'id' => $fu->id,
            'type' => 'followup',
            'date' => $fu->tanggal_followup,
            'model' => $fu,
        ]);
    }
    $timeline = $items->sortBy('date');

    $metodeIcon = [
        'telepon' => 'bi-telephone',
        'email' => 'bi-envelope',
        'kunjungan_langsung' => 'bi-people',
        'rapat' => 'bi-building',
        'whatsapp' => 'bi-whatsapp',
        'lainnya' => 'bi-chat-dots',
    ];

    $jenisSuratBadge = [
        'pemberitahuan' => 'bg-info text-dark',
        'peringatan' => 'bg-danger text-white',
        'konfirmasi' => 'bg-warning text-dark',
        'tagihan' => 'bg-secondary text-white',
        'lainnya' => 'bg-dark text-white',
    ];

    $statusBalasanBadge = [
        'positif' => 'bg-success text-white',
        'negatif' => 'bg-danger text-white',
        'netral' => 'bg-secondary text-white',
        'perlu_tindaklanjut' => 'bg-warning text-dark',
    ];

    $hasilFollowupBadge = [
        'berhasil_dihubungi' => 'bg-success text-white',
        'tidak_ada_respons' => 'bg-danger text-white',
        'dijadwalkan_ulang' => 'bg-info text-dark',
        'selesai' => 'bg-primary text-white',
    ];
@endphp

@if($timeline->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar-event fs-2 mb-2 d-block"></i>
        Belum ada riwayat surat atau follow-up untuk relasi ini.
    </div>
@else
    <div class="timeline-container position-relative">
        <div class="timeline-line position-absolute h-100" style="width: 2px; background: #e9ecef; left: 16px; top: 0; z-index: 0;"></div>

        @foreach($timeline as $item)
            <div class="timeline-item d-flex gap-3 mb-4 position-relative" style="z-index: 1;">
                <!-- Icon container -->
                <div class="timeline-icon d-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm border" style="width: 34px; height: 34px; flex-shrink: 0;">
                    @if($item->type === 'surat')
                        <i class="bi bi-envelope-paper text-primary fs-5"></i>
                    @else
                        <i class="bi {{ $metodeIcon[$item->model->metode] ?? 'bi-chat-left-text' }} text-warning fs-5"></i>
                    @endif
                </div>

                <!-- Content card -->
                <div class="flex-grow-1">
                    @if($item->type === 'surat')
                        <!-- SURAT CARD -->
                        <div class="card border-0 border-start border-4 border-primary shadow-sm">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge {{ $jenisSuratBadge[$item->model->jenis_surat] ?? 'bg-secondary' }} mb-1">
                                            Surat {{ ucfirst($item->model->jenis_surat) }}
                                        </span>
                                        <h6 class="mb-0 fw-semibold">{{ $item->model->perihal }}</h6>
                                        <small class="text-muted">No: {{ $item->model->nomor_surat ?? '—' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">{{ $item->model->tanggal_surat->format('d M Y') }}</small>
                                        @if($item->model->dikirimOleh)
                                            <small class="text-muted small">Oleh: {{ $item->model->dikirimOleh->name }}</small>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-secondary small mb-2" style="white-space: pre-wrap;">{{ $item->model->isi_ringkasan }}</p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($item->model->file_surat)
                                            <a href="{{ asset($item->model->file_surat) }}" target="_blank" class="btn btn-xs btn-outline-primary" style="padding:.2rem .4rem;font-size:.75rem">
                                                <i class="bi bi-file-pdf me-1"></i>Unduh Surat
                                            </a>
                                        @endif
                                        <button class="btn btn-xs btn-primary" onclick="openBalasanModal({{ $item->model->id }}, '{{ $item->model->perihal }}')" style="padding:.2rem .4rem;font-size:.75rem">
                                            <i class="bi bi-reply me-1"></i>Catat Balasan
                                        </button>
                                    </div>
                                    @if(auth()->user()->isAdmin())
                                        <button class="btn btn-xs text-danger border-0 p-0" onclick="deleteSurat({{ $item->model->id }})">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- BALASAN INDENTED -->
                        @if($item->model->ispBalasan->isNotEmpty())
                            @foreach($item->model->ispBalasan as $balasan)
                                <div class="card border-0 border-start border-4 border-success mt-2 ms-4 shadow-sm" style="background-color: #f0fdf4;">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <span class="badge {{ $statusBalasanBadge[$balasan->status_balasan] ?? 'bg-secondary' }} mb-1" style="font-size: .7rem">
                                                    Balasan: {{ str_replace('_', ' ', ucfirst($balasan->status_balasan)) }}
                                                </span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block" style="font-size: .75rem">{{ $balasan->tanggal_balasan->format('d M Y') }}</small>
                                                @if($balasan->dicatatOleh)
                                                    <small class="text-muted small" style="font-size: .7rem">Dicatat: {{ $balasan->dicatatOleh->name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-secondary small mb-2" style="white-space: pre-wrap; font-size: .8rem;">{{ $balasan->isi_ringkasan }}</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                @if($balasan->file_balasan)
                                                    <a href="{{ asset($balasan->file_balasan) }}" target="_blank" class="btn btn-xs btn-outline-success" style="padding:.15rem .3rem;font-size:.7rem">
                                                        <i class="bi bi-file-pdf me-1"></i>Unduh Balasan
                                                    </a>
                                                @endif
                                            </div>
                                            @if(auth()->user()->isAdmin())
                                                <button class="btn btn-xs text-danger border-0 p-0" onclick="deleteBalasan({{ $balasan->id }})" style="font-size: .7rem">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    @else
                        <!-- FOLLOW UP CARD -->
                        <div class="card border-0 border-start border-4 border-warning shadow-sm">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-warning text-dark mb-1">
                                            Follow-up: {{ ucfirst(str_replace('_', ' ', $item->model->metode)) }}
                                        </span>
                                        <h6 class="mb-0 fw-semibold">Catatan Kunjungan / Komunikasi</h6>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">{{ $item->model->tanggal_followup->format('d M Y') }}</small>
                                        @if($item->model->dilakukanOleh)
                                            <small class="text-muted small">Oleh: {{ $item->model->dilakukanOleh->name }}</small>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-secondary small mb-2" style="white-space: pre-wrap;">{{ $item->model->catatan }}</p>

                                <div class="mb-2">
                                    <span class="badge {{ $hasilFollowupBadge[$item->model->hasil] ?? 'bg-secondary' }}">
                                        Hasil: {{ ucfirst(str_replace('_', ' ', $item->model->hasil)) }}
                                    </span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($item->model->file_bukti)
                                            <a href="{{ asset($item->model->file_bukti) }}" target="_blank" class="btn btn-xs btn-outline-warning" style="padding:.2rem .4rem;font-size:.75rem">
                                                <i class="bi bi-file-earmark-image me-1"></i>Lihat Bukti
                                            </a>
                                        @endif
                                    </div>
                                    @if(auth()->user()->isAdmin())
                                        <button class="btn btn-xs text-danger border-0 p-0" onclick="deleteFollowup({{ $item->model->id }})">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
