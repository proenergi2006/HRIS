@extends('layouts.grain')
@section('title', 'Riwayat Perubahan Organisasi')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Struktur Organisasi</li>
        <li class="breadcrumb-item active">Riwayat Perubahan</li>
      </ol>
    </nav>

    <div class="h3 mb-4">Riwayat Perubahan Struktur Organisasi</div>

    <form method="GET" class="form-row align-items-end mb-4">
      <div class="form-group col-md-2 mb-2">
        <label class="small text-muted mb-1">Jenis Unit</label>
        <select name="unit_type" class="form-control">
          <option value="">Semua</option>
          @foreach(['branch' => 'Cabang', 'division' => 'Divisi', 'department' => 'Departemen', 'section' => 'Section', 'position' => 'Jabatan'] as $k => $v)
            <option value="{{ $k }}" @selected(request('unit_type') === $k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-2">
        <label class="small text-muted mb-1">Efektif dari</label>
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
            <th style="width:110px">Efektif</th>
            <th style="width:110px">Jenis</th>
            <th>Unit</th>
            <th style="width:110px">Aksi</th>
            <th>Perubahan</th>
            <th style="width:150px">Oleh</th>
          </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
          <tr>
            <td class="align-middle">{{ $log->effective_date?->format('d/m/Y') }}</td>
            <td class="align-middle">{{ $log->unit_type_label }}</td>
            <td class="align-middle">{{ $log->unit_name ?? '#' . $log->unit_id }}</td>
            <td class="align-middle"><span class="badge badge-light">{{ $log->action_label }}</span></td>
            <td class="align-middle small">
              @if($log->changes)
                @foreach($log->changes as $field => $ch)
                  <div><code>{{ $field }}</code>: {{ $ch['from'] ?? '∅' }} &rarr; {{ $ch['to'] ?? '∅' }}</div>
                @endforeach
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td class="align-middle small">{{ $log->changedBy?->name ?? 'Sistem' }}<br><span class="text-muted">{{ $log->created_at?->format('d/m/Y H:i') }}</span></td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-muted small py-3">Belum ada riwayat perubahan.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
  </div>
</div>
@endsection
