@extends('layouts.grain')
@section('title', 'Bonus / Insentif')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Bonus / Insentif</div>
  <a href="{{ route('hr.bonus.create') }}" class="btn btn-primary btn-sm"><i class="gd-plus mr-1"></i> Periode Baru</a>
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
      <p class="text-muted small mb-0">Belum ada periode bonus/insentif.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Nama</th><th>Jenis</th><th>Tgl Bayar</th><th class="text-center">Pajak</th><th class="text-center">Karyawan</th><th class="text-right">Total Bruto</th><th>Status</th><th></th></tr></thead>
          <tbody>
          @foreach($periods as $p)
            <tr>
              <td>{{ $p->name }}</td>
              <td>{{ $p->type_label }}</td>
              <td>{{ $p->payment_date?->format('d/m/Y') }}</td>
              <td class="text-center">{{ $p->is_taxable ? 'Ya' : 'Tidak' }}</td>
              <td class="text-center">{{ $p->payments_count }}</td>
              <td class="text-right">Rp {{ number_format($p->payments_sum_gross_amount ?? 0, 0, ',', '.') }}</td>
              <td><span class="badge badge-{{ $p->status === 'closed' ? 'success' : 'warning' }}">{{ $p->status === 'closed' ? 'Ditutup' : 'Terbuka' }}</span></td>
              <td><a href="{{ route('hr.bonus.show', $p) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
