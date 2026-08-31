@extends('layouts.grain')
@section('title', 'Kasbon / Pinjaman')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Kasbon / Pinjaman Karyawan</div>
  <a href="{{ route('hr.loans.create', ['company_id' => $companyId]) }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Catat Kasbon/Pinjaman
  </a>
</div>

<div class="alert alert-info py-2 px-3" style="font-size:.85rem">
  <i class="gd-info mr-1"></i> Cicilan otomatis dipotong saat generate slip gaji lewat komponen
  <strong>"Potongan Kasbon/Pinjaman"</strong>. Cicilan bulan tertentu ikut terpotong pada periode gaji dengan bulan/tahun yang sama.
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
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Status</label>
        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">Semua</option>
          @foreach(\App\Models\HR\EmployeeLoan::$statusLabels as $k => $v)
            <option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if($loans->isEmpty())
      <p class="text-muted small mb-0">Belum ada data kasbon/pinjaman.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr>
            <th>Karyawan</th><th>Jenis</th><th>No. Ref</th><th class="text-right">Pokok</th>
            <th class="text-center">Cicilan</th><th class="text-right">Sisa</th><th>Mulai</th><th>Status</th><th></th>
          </tr></thead>
          <tbody>
          @foreach($loans as $loan)
            @php $paid = $loan->installments->where('status', 'deducted')->count(); @endphp
            <tr>
              <td>{{ $loan->employee?->name }}<div class="small text-muted">{{ $loan->employee?->nip }}</div></td>
              <td>{{ $loan->type_label }}</td>
              <td class="small">{{ $loan->reference_no ?: '—' }}</td>
              <td class="text-right">Rp {{ number_format($loan->principal, 0, ',', '.') }}</td>
              <td class="text-center">{{ $paid }}/{{ $loan->installment_count }}</td>
              <td class="text-right">Rp {{ number_format($loan->outstanding(), 0, ',', '.') }}</td>
              <td class="small">{{ str_pad($loan->start_month, 2, '0', STR_PAD_LEFT) }}/{{ $loan->start_year }}</td>
              <td><span class="badge badge-{{ \App\Models\HR\EmployeeLoan::$statusBadges[$loan->status] ?? 'secondary' }}">{{ $loan->status_label }}</span></td>
              <td><a href="{{ route('hr.loans.show', $loan) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
