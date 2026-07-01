@extends('layouts.grain')
@section('title', 'Detail Laporan — ' . $report->ticket_number)

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('whistleblower.admin.index') }}">Whistleblower</a></li>
                <li class="breadcrumb-item active">{{ $report->ticket_number }}</li>
            </ol>
        </nav>

        <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <div class="h3 mb-0">{{ $report->ticket_number }}</div>
                <span class="badge badge-{{ $report->status_badge }} mt-1" style="font-size:.9rem;padding:5px 12px;">
                    {{ $report->status_label }}
                </span>
            </div>
            <a href="{{ route('whistleblower.admin.index') }}" class="btn btn-outline-secondary btn-sm mt-2">
                &larr; Kembali
            </a>
        </div>

        <div class="row">
            {{-- Detail laporan --}}
            <div class="col-md-8">
                {{-- Identitas Pelapor --}}
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold mb-2">Identitas Pelapor</div>
                        @if ($report->is_anonymous)
                            <span class="badge badge-secondary">Anonim</span>
                            <span class="text-muted ml-2">Pelapor memilih melapor secara anonim.</span>
                        @else
                            <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted pl-0" style="width:160px;">Nama</td>
                                    <td>{{ $report->reporter_name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-0">Email</td>
                                    <td>{{ $report->reporter_email ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-0">No. HP</td>
                                    <td>{{ $report->reporter_phone ?? '—' }}</td>
                                </tr>
                            </table>
                            </div>
                        @endif
                        @if ($report->reporter_relation)
                        <div class="mt-2">
                            <span class="text-muted small">Hubungan dengan Perusahaan:</span>
                            <span class="badge badge-info ml-1">{{ $report->reporter_relation }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Detail Laporan --}}
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted pl-0 font-weight-bold text-uppercase" style="font-size:.75rem;width:200px;">Kategori</td>
                                <td>{{ $report->category }}</td>
                            </tr>
                            @if ($report->branch_location)
                            <tr>
                                <td class="text-muted pl-0 font-weight-bold text-uppercase" style="font-size:.75rem;">Lokasi Cabang</td>
                                <td>{{ $report->branch_location }}</td>
                            </tr>
                            @endif
                            @if ($report->incident_location_time)
                            <tr>
                                <td class="text-muted pl-0 font-weight-bold text-uppercase" style="font-size:.75rem;">Waktu & Tempat</td>
                                <td>{{ $report->incident_location_time }}</td>
                            </tr>
                            @endif
                            @if ($report->suspected_parties)
                            <tr>
                                <td class="text-muted pl-0 font-weight-bold text-uppercase" style="font-size:.75rem;">Pihak yang Diduga</td>
                                <td>{{ $report->suspected_parties }}</td>
                            </tr>
                            @endif
                            @if ($report->witnesses)
                            <tr>
                                <td class="text-muted pl-0 font-weight-bold text-uppercase" style="font-size:.75rem;">Saksi / Pihak Lain</td>
                                <td>{{ $report->witnesses }}</td>
                            </tr>
                            @endif
                            @if ($report->previously_reported !== null)
                            <tr>
                                <td class="text-muted pl-0 font-weight-bold text-uppercase" style="font-size:.75rem;">Pernah Melapor</td>
                                <td>
                                    @if ($report->previously_reported === 'sudah')
                                        <span class="badge badge-warning">Sudah Pernah</span>
                                    @else
                                        <span class="badge badge-secondary">Belum Pernah</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if ($report->willing_to_be_contacted !== null)
                            <tr>
                                <td class="text-muted pl-0 font-weight-bold text-uppercase" style="font-size:.75rem;">Bersedia Dihubungi</td>
                                <td>
                                    @if ($report->willing_to_be_contacted)
                                        <span class="badge badge-success">Ya</span>
                                    @else
                                        <span class="badge badge-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </table>
                        </div>
                    </div>
                </div>

                {{-- Uraian Kejadian --}}
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold mb-1">Uraian Kejadian</div>
                        <div style="white-space:pre-wrap;line-height:1.7;">{{ $report->description }}</div>
                    </div>
                </div>

                @if ($report->attachment_path)
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold mb-1">Lampiran</div>
                        <a href="{{ route('whistleblower.admin.download', $report) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="gd-download icon-text"></i>
                            {{ $report->attachment_original_name ?? 'Unduh Lampiran' }}
                        </a>
                    </div>
                </div>
                @endif
            </div>

            {{-- Panel kanan: update status --}}
            <div class="col-md-4 mt-3 mt-md-0">
                <div class="card">
                    <div class="card-header font-weight-bold">Update Status</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('whistleblower.admin.update-status', $report) }}">
                            @csrf @method('PATCH')
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control form-control-sm">
                                    @foreach (\App\Models\WhistleblowerReport::$statuses as $key => $s)
                                        <option value="{{ $key }}" {{ $report->status === $key ? 'selected' : '' }}>
                                            {{ $s['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Catatan Admin <small class="text-muted">(internal)</small></label>
                                <textarea name="admin_notes" rows="4" class="form-control form-control-sm"
                                    placeholder="Catatan tindak lanjut...">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm btn-block">Simpan</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold mb-2">Info Laporan</div>
                        <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted pl-0" style="width:100px;">Masuk</td>
                                <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if ($report->reviewed_at)
                            <tr>
                                <td class="text-muted pl-0">Ditinjau</td>
                                <td>{{ $report->reviewed_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
