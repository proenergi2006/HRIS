@extends('layouts.grain')
@section('title', 'Laporan Whistleblower')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Whistleblower</li>
            </ol>
        </nav>

        <div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="h3 mb-0">Laporan Pengaduan</div>
                <small class="text-muted">Whistleblower System — PT. Pro Energi</small>
            </div>
            <a href="{{ route('whistleblower.admin.qrcode') }}" class="btn btn-outline-secondary">
                <i class="gd-qr-code icon-text"></i> QR Code
            </a>
        </div>

        {{-- Summary badges --}}
        <div class="mb-3">
            @foreach (\App\Models\WhistleblowerReport::$statuses as $key => $s)
                @php $cnt = \App\Models\WhistleblowerReport::where('status', $key)->count(); @endphp
                <span class="badge badge-{{ $s['badge'] }} mr-1" style="font-size:.85rem;padding:5px 10px;">
                    {{ $s['label'] }}: {{ $cnt }}
                </span>
            @endforeach
        </div>

        {{-- Filter --}}
        <form method="GET" class="mb-3">
            <div class="form-row align-items-end">
                <div class="form-group col-12 col-md-3 mb-2">
                    <label class="mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\WhistleblowerReport::$statuses as $key => $s)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $s['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-3 mb-2">
                    <label class="mb-1">Kategori</label>
                    <select name="category" class="form-control form-control-sm">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-auto mb-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['status','category']))
                        <a href="{{ route('whistleblower.admin.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive-xl">
            <table id="dt-wb" class="table mb-0">
                <thead>
                    <tr>
                        <th class="font-weight-semi-bold border-top-0 py-2">No. Tiket</th>
                        <th class="font-weight-semi-bold border-top-0 py-2">Kategori</th>
                        <th class="font-weight-semi-bold border-top-0 py-2">Pelapor</th>
                        <th class="font-weight-semi-bold border-top-0 py-2">Lampiran</th>
                        <th class="font-weight-semi-bold border-top-0 py-2">Status</th>
                        <th class="font-weight-semi-bold border-top-0 py-2">Tanggal</th>
                        <th class="font-weight-semi-bold border-top-0 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($reports as $r)
                    <tr>
                        <td class="py-3 font-weight-bold font-monospace">{{ $r->ticket_number }}</td>
                        <td class="py-3">{{ $r->category }}</td>
                        <td class="py-3">
                            @if ($r->is_anonymous)
                                <span class="badge badge-secondary">Anonim</span>
                            @else
                                <div>{{ $r->reporter_name ?? '-' }}</div>
                                @if($r->reporter_email)<small class="text-muted">{{ $r->reporter_email }}</small>@endif
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if ($r->attachment_path)
                                <span class="badge badge-info">Ada</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="badge badge-{{ $r->status_badge }}">{{ $r->status_label }}</span>
                        </td>
                        <td class="py-3">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3">
                            <a href="{{ route('whistleblower.admin.show', $r) }}"
                               class="btn btn-sm btn-outline-info">
                                <i class="gd-eye icon-text"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function(){
    $('#dt-wb').DataTable({
        language: window.siproDtLang,
        order: [[5,'desc']],
        columnDefs: [{ orderable: false, targets: -1 }]
    });
});
</script>
@endsection
