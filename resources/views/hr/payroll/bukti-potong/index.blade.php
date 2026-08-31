@extends('layouts.grain')
@section('title', 'Bukti Potong PPh21 Tahunan')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.payroll.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Penggajian</a></div>
<div class="h3 mb-3">Bukti Potong PPh21 Tahunan</div>

<div class="alert alert-info py-2 px-3" style="font-size:.85rem">
  <i class="gd-info mr-1"></i> Ringkasan bruto kena pajak & PPh21 yang dipotong dari slip gaji <strong>closed</strong>
  dalam 1 tahun pajak (termasuk pajak bonus/insentif taxable). Format internal — bukan Form 1721-A1 resmi DJP.
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
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Tahun Pajak</label>
        <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" onchange="this.form.submit()">
      </div>
    </form>
    @if($company && ! $company->npwp)
      <div class="small text-warning mt-2"><i class="gd-alert mr-1"></i> NPWP & penandatangan perusahaan belum diisi di Master Data — akan kosong di PDF.</div>
    @endif
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if(empty($rows))
      <p class="text-muted small mb-0">Belum ada slip gaji closed untuk perusahaan & tahun ini.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr>
            <th>Karyawan</th><th>PTKP</th><th class="text-center">Masa</th>
            <th class="text-right">Bruto Kena Pajak</th><th class="text-right">PPh21 Dipotong</th><th></th>
          </tr></thead>
          <tbody>
          @foreach($rows as $r)
            <tr>
              <td>{{ $r['employee']->name }}<div class="small text-muted">{{ $r['employee']->nip }}</div></td>
              <td>{{ $r['ptkp_status'] }}</td>
              <td class="text-center small">{{ \Carbon\Carbon::create()->month($r['month_from'])->translatedFormat('M') }}–{{ \Carbon\Carbon::create()->month($r['month_to'])->translatedFormat('M') }} ({{ $r['period_count'] }})</td>
              <td class="text-right">Rp {{ number_format($r['bruto_taxable'], 0, ',', '.') }}</td>
              <td class="text-right">Rp {{ number_format($r['pph21'], 0, ',', '.') }}</td>
              <td><a href="{{ route('hr.payroll.bukti-potong.pdf', [$companyId, $r['employee_id'], $year]) }}" class="btn btn-xs btn-outline-secondary">PDF</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
