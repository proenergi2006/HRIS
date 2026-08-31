@extends('layouts.grain')
@section('title', 'Riwayat Perubahan Alur Persetujuan')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('approval.workflows.index') }}">Pengaturan Approval</a></li>
        <li class="breadcrumb-item active">Riwayat Perubahan</li>
      </ol>
    </nav>

    <div class="h3 mb-4">Riwayat Perubahan Alur Persetujuan</div>

    <form method="GET" class="form-row align-items-end mb-4">
      <div class="form-group col-md-2 mb-2">
        <label class="small text-muted mb-1">Perusahaan</label>
        <select name="company_id" class="form-control">
          <option value="">Semua</option>
          @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small text-muted mb-1">Jenis Transaksi</label>
        <select name="transaction_type" class="form-control">
          <option value="">Semua</option>
          @foreach($types as $k => $v)
            <option value="{{ $k }}" @selected(request('transaction_type') === $k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-2">
        <label class="small text-muted mb-1">Dari tanggal</label>
        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
      </div>
      <div class="form-group col-md-2 mb-2">
        <label class="small text-muted mb-1">s/d</label>
        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
      </div>
      <div class="form-group col-auto mb-2">
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width:130px">Waktu</th>
            <th style="width:120px">Perusahaan</th>
            <th>Jenis Transaksi</th>
            <th style="width:90px">Aksi</th>
            <th>Perubahan</th>
            <th style="width:150px">Oleh</th>
          </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
          <tr>
            <td class="align-middle small">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
            <td class="align-middle small">{{ $log->company?->short_name ?? $log->company?->name ?? '-' }}</td>
            <td class="align-middle">{{ $log->transaction_label }}</td>
            <td class="align-middle"><span class="badge badge-light">{{ $log->action_label }}</span></td>
            <td class="align-middle small">
              <div class="d-flex" style="gap:1.5rem">
                <div>
                  <div class="text-muted font-weight-bold">Sebelum</div>
                  @forelse($log->before ?? [] as $s)
                    <div>{{ $s['urutan'] }}. {{ $s['approver'] }}@if(!empty($s['kondisi'])) <span class="text-muted">[jika {{ $s['kondisi'] }}]</span>@endif @if(!empty($s['eskalasi_hari'])) <span class="text-muted">· eskalasi {{ $s['eskalasi_hari'] }}h</span>@endif</div>
                  @empty
                    <div class="text-muted">&mdash;</div>
                  @endforelse
                </div>
                <div>
                  <div class="text-muted font-weight-bold">Sesudah</div>
                  @forelse($log->after ?? [] as $s)
                    <div>{{ $s['urutan'] }}. {{ $s['approver'] }}@if(!empty($s['kondisi'])) <span class="text-muted">[jika {{ $s['kondisi'] }}]</span>@endif @if(!empty($s['eskalasi_hari'])) <span class="text-muted">· eskalasi {{ $s['eskalasi_hari'] }}h</span>@endif</div>
                  @empty
                    <div class="text-muted">&mdash;</div>
                  @endforelse
                </div>
              </div>
              @if($log->note)<div class="text-muted mt-1"><em>{{ $log->note }}</em></div>@endif
            </td>
            <td class="align-middle small">{{ $log->changedBy?->name ?? 'Sistem' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-muted small py-3">Belum ada riwayat perubahan alur persetujuan.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
  </div>
</div>
@endsection
