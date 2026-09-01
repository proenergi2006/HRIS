@extends('layouts.grain')
@section('title', 'Cuti & Izin Saya')

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <div class="h3 mb-0">Cuti &amp; Izin Saya</div>
  <a href="{{ route('leave.mine.create') }}" class="btn btn-primary btn-sm"><i class="gd-plus mr-1"></i>Ajukan Cuti/Izin</a>
</div>

<div class="row mb-4">
  @foreach($leaveTypes as $t)
    @php $b = $balances[$t->id]; @endphp
    <div class="col-6 col-md-3 mb-3">
      <div class="card h-100"><div class="card-body py-3">
        <div class="small text-muted font-weight-bold mb-1">{{ $t->name }}</div>
        <div class="h4 mb-0">{{ $b->getRemaining() }} <span class="small text-muted">/ {{ $b->allocated }} hari</span></div>
      </div></div>
    </div>
  @endforeach
</div>

<form method="GET" class="form-inline mb-3">
  <label class="mr-2 small font-weight-bold">Tahun</label>
  <input type="number" name="year" class="form-control form-control-sm" style="width:100px" value="{{ $year }}" onchange="this.form.submit()">
</form>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th class="pl-3">Jenis</th><th>Mulai</th><th>Selesai</th><th class="text-right">Hari</th><th>Status</th><th style="width:80px"></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
          <tr>
            <td class="pl-3">{{ $r->leaveType?->name ?? '-' }}</td>
            <td class="small">{{ $r->start_date->format('d/m/Y') }}</td>
            <td class="small">{{ $r->end_date->format('d/m/Y') }}</td>
            <td class="text-right">{{ $r->total_days }}</td>
            <td><span class="badge badge-{{ \App\Models\HR\LeaveRequest::$statusBadges[$r->status] ?? 'secondary' }}">{{ \App\Models\HR\LeaveRequest::$statusLabels[$r->status] ?? ucfirst($r->status) }}</span></td>
            <td class="pr-3 text-right"><a href="{{ route('leave.mine.show', $r) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-muted small p-3">Belum ada pengajuan cuti/izin tahun {{ $year }}.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
