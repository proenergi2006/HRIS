@extends('layouts.grain')
@section('title', 'Career Management')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Career Management</div>
  @can('career.edit')
  <a href="{{ route('career.paths.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-settings mr-1"></i> Kelola Career Path
  </a>
  @endcan
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if($employees->isEmpty())
      <p class="text-muted small mb-0">Belum ada karyawan aktif di perusahaan ini.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Karyawan</th><th>Jabatan Sekarang</th><th>Career Path</th><th class="text-center">Riwayat Jabatan</th><th></th></tr></thead>
          <tbody>
          @foreach($employees as $e)
            <tr>
              <td>{{ $e->name }}</td>
              <td>{{ $e->position?->name ?? '—' }}</td>
              <td>{{ $e->careerPath?->title ?? '—' }}</td>
              <td class="text-center">{{ $e->org_experiences_count }}</td>
              <td><a href="{{ route('career.show', $e) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
