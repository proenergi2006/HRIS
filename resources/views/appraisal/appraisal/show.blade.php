@extends('layouts.grain')
@section('title', 'Detail Penilaian')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appraisal.appraisals.index') }}">Data Penilaian</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="mb-4 d-flex justify-content-between align-items-start">
            <div>
                <div class="h3 mb-1">Penilaian Kinerja Karyawan</div>
                <div class="text-muted">
                    <strong>{{ $appraisal->employee->name }}</strong>
                    &nbsp;·&nbsp; {{ $appraisal->employee->position }}
                    @if($appraisal->employee->department)
                        &nbsp;·&nbsp; {{ $appraisal->employee->department }}
                    @endif
                </div>
                <div class="text-muted small mt-1">
                    Periode: <strong>{{ $appraisal->period->name }}</strong>
                    &nbsp;·&nbsp; Template: {{ $appraisal->template->name }}
                </div>
            </div>
            <div class="text-right">
                @php
                    $badgeClass = match($appraisal->status) {
                        'draft'          => 'secondary',
                        'submitted'      => 'warning',
                        'approved_user2' => 'info',
                        'approved_cfo'   => 'success',
                        'rejected'       => 'danger',
                        default          => 'secondary',
                    };
                @endphp
                <span class="badge badge-{{ $badgeClass }} badge-lg" style="font-size:0.9rem;padding:6px 12px;">
                    {{ $appraisal->status_label }}
                </span>
            </div>
        </div>

        {{-- Aspek Penilaian --}}
        @if($appraisal->template->isWeightedScale())
            {{-- ── WEIGHTED SCALE (Staff / Senior Staff) ── --}}
            @php
                $evalLabels  = ['self' => 'Diri Sendiri', 'atasan1' => 'Atasan I', 'atasan2' => 'Atasan II', 'ho' => 'Head Office'];
                $ratingLabels = [1 => 'Kurang Sekali', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Baik Sekali'];
            @endphp
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th rowspan="2" style="width:32px;vertical-align:middle">#</th>
                            <th rowspan="2" style="vertical-align:middle">Faktor Penilaian</th>
                            <th rowspan="2" class="text-center" style="width:60px;vertical-align:middle">Bobot</th>
                            @foreach($evalLabels as $label)
                                <th class="text-center" style="width:100px">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($appraisal->template->aspects as $aspect)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle">{{ $aspect->name }}</td>
                            <td class="text-center align-middle">
                                <span class="badge badge-light">{{ $aspect->weight_pct }}%</span>
                            </td>
                            @foreach(array_keys($evalLabels) as $evalType)
                                @php $item = $itemsByEvaluator->get($evalType)?->get($aspect->id); @endphp
                                <td class="text-center align-middle">
                                    @if($item?->rating)
                                        <span class="badge badge-primary">{{ $item->rating }}</span>
                                        <div class="small text-muted" style="font-size:0.7rem;">{{ $ratingLabels[(int)$item->rating] ?? '' }}</div>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <td colspan="3" class="text-right font-weight-bold py-2">Skor per Penilai</td>
                            @foreach(array_keys($evalLabels) as $evalType)
                                <td class="text-center font-weight-bold">
                                    {{ number_format($appraisal->{'score_'.$evalType} ?? 0, 0) }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right font-weight-bold py-2">Total Skor (rata-rata)</td>
                            <td colspan="4" class="text-center font-weight-bold py-2">
                                {{ number_format($appraisal->total_score ?? 0, 1) }}
                                &nbsp; <span class="badge badge-{{ $appraisal->grade ? 'success' : 'secondary' }}">{{ $appraisal->grade ?? '-' }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Kualitatif --}}
            @if($appraisal->strength_points || $appraisal->development_need || $appraisal->individual_development_plan)
            <div class="card card-frame mb-4">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Penilaian Kualitatif</h6>
                    @if($appraisal->strength_points)
                    <div class="mb-3">
                        <div class="text-muted small font-weight-bold mb-1">Strength Point</div>
                        <p class="mb-0">{{ $appraisal->strength_points }}</p>
                    </div>
                    @endif
                    @if($appraisal->development_need)
                    <div class="mb-3">
                        <div class="text-muted small font-weight-bold mb-1">Development Need Area</div>
                        <p class="mb-0">{{ $appraisal->development_need }}</p>
                    </div>
                    @endif
                    @if($appraisal->individual_development_plan)
                    <div>
                        <div class="text-muted small font-weight-bold mb-1">Individual Development Plan (IDP)</div>
                        <p class="mb-0">{{ $appraisal->individual_development_plan }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        @else
            {{-- ── FIXED POINTS (SPV / Manager / Admin) ── --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Aspek Penilaian</th>
                            <th class="text-center" style="width:90px">Baik Sekali (BS)</th>
                            <th class="text-center" style="width:80px">Baik (B)</th>
                            <th class="text-center" style="width:80px">Cukup (C)</th>
                            <th class="text-center" style="width:80px">Kurang (K)</th>
                            <th class="text-center" style="width:80px">Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($appraisal->template->aspects as $aspect)
                        @php $item = $itemsByAspect->get($aspect->id); @endphp
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle">{{ $aspect->name }}</td>
                            @foreach(['BS','B','C','K'] as $rating)
                                <td class="text-center align-middle">
                                    @if($item?->rating === $rating)
                                        <span class="text-success font-weight-bold" style="font-size:1.1rem;">&#10003;</span>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center align-middle font-weight-bold">{{ $item?->score ?? 0 }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <td colspan="6" class="text-right font-weight-bold py-2">Total Skor</td>
                            <td class="text-center font-weight-bold py-2">{{ $appraisal->total_score }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-right font-weight-bold py-2">Grade</td>
                            <td class="text-center font-weight-bold py-2">{{ $appraisal->grade ?? '-' }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Data Absensi & Usulan --}}
            <div class="row mb-4">
                <div class="col-12 col-md-6">
                    <div class="card card-frame h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-3">Data Absensi</h6>
                            <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Rata-rata Keterlambatan</td>
                                    <td class="font-weight-bold">{{ $appraisal->avg_late_per_month }} hari/bulan</td>
                                </tr>
                                <tr>
                                    <td>Rata-rata Tidak Hadir</td>
                                    <td class="font-weight-bold">{{ $appraisal->avg_leave_per_month }} hari/bulan</td>
                                </tr>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mt-3 mt-md-0">
                    <div class="card card-frame h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-3">Usulan</h6>
                            <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Surat Teguran</td>
                                    <td class="font-weight-bold">{{ $appraisal->warning_letter ? 'Ya' : 'Tidak' }}</td>
                                </tr>
                                <tr>
                                    <td>Surat Peringatan</td>
                                    <td class="font-weight-bold">{{ $appraisal->sp_level_label }}</td>
                                </tr>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($appraisal->decision)
            <div class="form-group">
                <label class="font-weight-bold">Keputusan / Rekomendasi</label>
                <p class="border rounded p-3 mb-0"
                   style="background:#f0fdf4;border-color:#86efac!important;color:#166534;font-weight:600;">
                    {{ $appraisal->decision }}
                </p>
            </div>
        @endif

        @if($appraisal->notes)
            <div class="form-group">
                <label class="font-weight-bold">Catatan Evaluator</label>
                <p class="border rounded p-3 bg-light mb-0">{{ $appraisal->notes }}</p>
            </div>
        @endif

        {{-- Audit trail timeline --}}
        <div class="mt-4">
            <h6 class="font-weight-bold mb-3">Riwayat Proses</h6>
            <div class="sipro-timeline">
                {{-- Created --}}
                <div class="sipro-tl-item">
                    <div class="sipro-tl-dot sipro-tl-info"></div>
                    <div class="sipro-tl-body">
                        <div class="sipro-tl-title">Penilaian Dibuat</div>
                        <div class="sipro-tl-meta">
                            {{ $appraisal->created_at->format('d M Y, H:i') }}
                            @if($appraisal->evaluator)
                                &bull; oleh <strong>{{ $appraisal->evaluator->name }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- Approvals --}}
                @foreach($appraisal->approvals->sortBy('created_at') as $approval)
                @php
                    $dot = match($approval->action) {
                        'approve' => 'sipro-tl-success',
                        'reject'  => 'sipro-tl-danger',
                        'submit'  => 'sipro-tl-primary',
                        default   => 'sipro-tl-muted',
                    };
                    $actionLabel = match($approval->action) {
                        'approve' => 'Disetujui',
                        'reject'  => 'Dikembalikan',
                        'submit'  => 'Disubmit',
                        default   => ucfirst($approval->action),
                    };
                @endphp
                <div class="sipro-tl-item">
                    <div class="sipro-tl-dot {{ $dot }}"></div>
                    <div class="sipro-tl-body">
                        <div class="sipro-tl-title">
                            {{ $actionLabel }}
                            <span class="badge badge-{{ $approval->action === 'approve' ? 'success' : ($approval->action === 'reject' ? 'danger' : ($approval->action === 'submit' ? 'primary' : 'secondary')) }} ml-1" style="font-size:.7rem;">{{ $approval->role }}</span>
                        </div>
                        <div class="sipro-tl-meta">
                            {{ $approval->created_at->format('d M Y, H:i') }}
                            @if($approval->user) &bull; oleh <strong>{{ $approval->user->name }}</strong> @endif
                        </div>
                        @if($approval->notes)
                            <div class="sipro-tl-note">"{{ $approval->notes }}"</div>
                        @endif
                    </div>
                </div>
                @endforeach
                @if($appraisal->approvals->isEmpty())
                    <div class="sipro-tl-item">
                        <div class="sipro-tl-dot sipro-tl-muted"></div>
                        <div class="sipro-tl-body">
                            <div class="sipro-tl-meta text-muted">Belum ada aksi persetujuan.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info untuk karyawan --}}
        @if(auth()->user()->hasRole('karyawan') && $appraisal->isDraft())
        <div class="alert alert-success py-2 mt-3">
            <i class="gd-check mr-1"></i>
            <strong>Self-assessment Anda sudah tersimpan.</strong>
            Evaluator/atasan Anda akan melengkapi penilaian dan mengajukan ke persetujuan.
        </div>
        @endif

        {{-- Next approver info --}}
        @if($nextLabel)
            <div class="alert alert-info py-2 mt-3">
                <i class="gd-info mr-1"></i>
                Menunggu persetujuan: <strong>{{ $nextLabel }}</strong>
            </div>
        @endif

        @if($canApprove)
        <div class="alert alert-warning py-2">
            <i class="gd-alert mr-1"></i> Penilaian ini menunggu persetujuan Anda.
        </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-3">
            <a href="{{ route('appraisal.appraisals.index') }}" class="btn btn-secondary">Kembali</a>

            <div class="d-flex" style="gap:8px;">
                <a href="{{ route('appraisal.appraisals.pdf', $appraisal) }}"
                   class="btn btn-outline-secondary" target="_blank">
                    <i class="gd-download mr-1"></i> Download PDF
                </a>

                @if($appraisal->isDraft())
                    <a href="{{ route('appraisal.appraisals.edit', $appraisal) }}" class="btn btn-outline-primary">
                        <i class="gd-pencil mr-1"></i> Edit
                    </a>
                @endif

                @if($canSubmit)
                    <form id="form-submit-{{ $appraisal->id }}"
                          action="{{ route('appraisal.appraisals.submit', $appraisal) }}" method="POST">
                        @csrf
                        <button type="button" class="btn btn-primary"
                            data-confirm="Submit penilaian {{ $appraisal->employee->name }} untuk persetujuan? Setelah disubmit, data tidak dapat diedit kembali."
                            data-confirm-title="Submit Penilaian"
                            data-confirm-type="primary"
                            data-confirm-ok="Ya, Submit"
                            data-form="form-submit-{{ $appraisal->id }}">
                            <i class="gd-arrow-right mr-1"></i> Submit untuk Persetujuan
                        </button>
                    </form>
                @endif

                @if($canReject)
                    <button type="button" class="btn btn-outline-danger"
                            onclick="openSiproModal('modalReject')">
                        <i class="gd-close mr-1"></i> Tolak
                    </button>
                @endif

                @if($canApprove)
                    <button type="button" class="btn btn-success"
                            onclick="openSiproModal('modalApprove')">
                        <i class="gd-check mr-1"></i> Setujui
                    </button>
                @endif
            </div>
        </div>

    </div>{{-- /card-body --}}
</div>{{-- /card --}}
@endsection

{{-- Modals langsung ke <body> via @push agar tidak ada stacking context conflict --}}

@if($canApprove)
@push('modals')
<div id="modalApprove" class="sipro-overlay">
    <div class="sipro-backdrop" onclick="closeSiproModal('modalApprove')"></div>
    <div class="sipro-dialog">
        <form action="{{ route('appraisal.appraisals.approve', $appraisal) }}" method="POST">
            @csrf
            <div class="sipro-header">
                <h5>Konfirmasi Persetujuan</h5>
                <button type="button" class="sipro-close" onclick="closeSiproModal('modalApprove')">&times;</button>
            </div>
            <div class="sipro-body">
                <p class="mb-3">Anda akan menyetujui penilaian kinerja
                   <strong>{{ $appraisal->employee->name }}</strong>
                   — {{ $appraisal->period->name }}.</p>

                @if($isFinalStep)
                <div class="form-group">
                    <label for="decision">Keputusan / Rekomendasi <span class="text-danger">*</span></label>
                    <select name="decision" id="decision" class="form-control" required>
                        <option value="">-- Pilih Keputusan --</option>
                        <option value="Diperpanjang">Diperpanjang</option>
                        <option value="Diangkat Karyawan Tetap">Diangkat Karyawan Tetap</option>
                        <option value="Dipertahankan">Dipertahankan</option>
                        <option value="Perlu Pembinaan">Perlu Pembinaan</option>
                        <option value="Tidak Diperpanjang">Tidak Diperpanjang</option>
                    </select>
                </div>
                @endif

                <div class="form-group mb-0">
                    <label for="notes_approve">Catatan (opsional)</label>
                    <textarea id="notes_approve" name="notes" rows="3" class="form-control"
                              placeholder="Tambahkan catatan persetujuan..."></textarea>
                </div>
            </div>
            <div class="sipro-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="closeSiproModal('modalApprove')">Batal</button>
                <button type="submit" class="btn btn-success">
                    {{ $isFinalStep ? 'Setujui Final' : 'Setujui' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endpush
@endif

@if($canReject)
@push('modals')
<div id="modalReject" class="sipro-overlay">
    <div class="sipro-backdrop" onclick="closeSiproModal('modalReject')"></div>
    <div class="sipro-dialog">
        <form action="{{ route('appraisal.appraisals.reject', $appraisal) }}" method="POST">
            @csrf
            <div class="sipro-header">
                <h5>Tolak Penilaian</h5>
                <button type="button" class="sipro-close" onclick="closeSiproModal('modalReject')">&times;</button>
            </div>
            <div class="sipro-body">
                <p class="mb-2">Penilaian akan dikembalikan ke evaluator untuk diperbaiki.</p>
                <div class="form-group mb-0">
                    <label for="notes_reject">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea id="notes_reject" name="notes" rows="3" class="form-control" required
                              placeholder="Jelaskan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="sipro-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="closeSiproModal('modalReject')">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak &amp; Kembalikan</button>
            </div>
        </form>
    </div>
</div>
@endpush
@endif
