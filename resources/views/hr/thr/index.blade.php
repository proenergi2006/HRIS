@extends('layouts.grain')
@section('title', 'THR')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">THR (Tunjangan Hari Raya)</div>
  <a href="{{ route('hr.thr.create') }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Periode THR Baru
  </a>
</div>

<div class="alert alert-info py-2 px-3" style="font-size:.85rem">
  <i class="gd-info mr-1"></i> Dihitung sesuai Permenaker No. 6/2016: karyawan masa kerja minimal 1 bulan
  berhak THR proporsional (bulan kerja ÷ 12) × (Gaji Pokok + Tunjangan Jabatan), penuh 1x gaji kalau masa kerja ≥12 bulan.
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
    @if($periods->isEmpty())
      <p class="text-muted small mb-0">Belum ada periode THR untuk perusahaan ini.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Hari Raya</th><th>Tahun</th><th>Tgl Bayar</th><th class="text-center">Karyawan</th><th class="text-right">Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
          @foreach($periods as $p)
            <tr>
              <td>{{ $p->holiday_name }}</td>
              <td>{{ $p->year }}</td>
              <td>{{ $p->payment_date?->format('d/m/Y') }}</td>
              <td class="text-center">{{ $p->payments_count }}</td>
              <td class="text-right">Rp {{ number_format($p->payments_sum_thr_amount ?? 0, 0, ',', '.') }}</td>
              <td><span class="badge badge-{{ $p->status === 'closed' ? 'success' : 'warning' }}">{{ $p->status === 'closed' ? 'Ditutup' : 'Terbuka' }}</span></td>
              <td><a href="{{ route('hr.thr.show', $p) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
