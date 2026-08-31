@extends('layouts.grain')
@section('title', 'Succession Planning — Jabatan Kritikal')

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <div class="h3 mb-0">Succession Planning — Jabatan Kritikal</div>
  <a href="{{ route('succession.matrix') }}" class="btn btn-sm btn-outline-primary"><i class="gd-target mr-1"></i>Ringkasan Kesiapan</a>
</div>

<div class="alert alert-info small mb-4">
  Tandai jabatan yang berisiko tinggi bila kosong mendadak (jabatan kunci, sulit dicari
  penggantinya, atau berdampak besar ke operasional) sebagai <strong>Jabatan Kritikal</strong>,
  lalu kelola daftar kandidat pengganti (talent pool) di halaman detail jabatan.
</div>

<form method="GET" class="form-inline mb-3" style="gap:.5rem">
  <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="">— Konsolidasi Grup —</option>
    @foreach($companies as $c)
      <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>
    @endforeach
  </select>
  <div class="custom-control custom-checkbox ml-2">
    <input type="checkbox" class="custom-control-input" id="criticalOnly" name="critical_only" value="1" @checked($onlyCritical) onchange="this.form.submit()">
    <label class="custom-control-label" for="criticalOnly">Hanya jabatan kritikal</label>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>Jabatan</th><th>Departemen</th><th>Level</th>
            <th class="text-center" style="width:90px">Kritikal?</th>
            <th style="width:130px">Risiko</th>
            <th>Catatan</th>
            <th class="text-center" style="width:90px">Talent Pool</th>
            <th style="width:70px"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($positions as $p)
          @php $f = 'pos-form-' . $p->id; @endphp
          <tr>
            <td class="font-weight-bold">{{ $p->name }}</td>
            <td class="small text-muted">{{ $p->department?->name ?? '—' }}</td>
            <td class="small">{{ $p->level?->name ?? '—' }}</td>
            <td class="text-center">
              <input type="checkbox" name="is_critical_position" form="{{ $f }}" value="1" @checked($p->is_critical_position)>
            </td>
            <td>
              <select name="succession_risk" form="{{ $f }}" class="form-control form-control-sm">
                <option value="">—</option>
                @foreach(\App\Models\Position::$successionRiskLabels as $k => $lbl)
                  <option value="{{ $k }}" @selected($p->succession_risk === $k)>{{ $lbl }}</option>
                @endforeach
              </select>
            </td>
            <td><input type="text" name="succession_notes" form="{{ $f }}" value="{{ $p->succession_notes }}" class="form-control form-control-sm"></td>
            <td class="text-center">
              <span class="badge badge-{{ $p->talent_pool_count > 0 ? 'success' : 'secondary' }}">{{ $p->talent_pool_count }}</span>
            </td>
            <td class="text-right">
              <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-primary">Simpan</button>
              <a href="{{ route('succession.show', $p) }}" class="btn btn-xs btn-outline-secondary">Detail</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-muted small p-3">Tidak ada jabatan.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@foreach($positions as $p)
  <form id="pos-form-{{ $p->id }}" method="POST" action="{{ route('succession.positions.update', $p) }}" class="d-none">@csrf @method('PUT')</form>
@endforeach
@endsection
