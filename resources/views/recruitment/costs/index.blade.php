@extends('layouts.grain')
@section('title', 'Biaya Rekrutmen')

@section('content')
@include('components.notification')

<div class="h3 mb-3">Biaya Rekrutmen</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)<option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header py-2"><strong class="small">Catat Biaya</strong></div>
  <div class="card-body">
    <form method="POST" action="{{ route('recruitment.costs.store') }}" class="form-row align-items-end">
      @csrf
      <input type="hidden" name="company_id" value="{{ $companyId }}">
      <div class="form-group col-md-3"><label class="small">Kategori</label>
        <select name="category" class="form-control form-control-sm" required>@foreach(\App\Models\RecruitmentCost::$categoryLabels as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
      </div>
      <div class="form-group col-md-3"><label class="small">Job Requisition</label>
        <select name="job_requisition_id" class="form-control form-control-sm">
          <option value="">— umum —</option>
          @foreach($requisitions as $r)<option value="{{ $r->id }}">{{ $r->title ?? ('#' . $r->id) }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2"><label class="small">Jumlah (Rp)</label><input type="number" data-rupiah name="amount" min="0" class="form-control form-control-sm" required></div>
      <div class="form-group col-md-2"><label class="small">Tanggal</label><input type="date" name="incurred_on" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required></div>
      <div class="form-group col-md-2"><button class="btn btn-primary btn-sm btn-block">Simpan</button></div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="thead-light"><tr><th>Tanggal</th><th>Kategori</th><th>Requisition</th><th class="text-right">Jumlah</th><th></th></tr></thead>
      <tbody>
      @forelse($costs as $c)
        <tr>
          <td>{{ $c->incurred_on->format('d/m/Y') }}</td>
          <td>{{ \App\Models\RecruitmentCost::$categoryLabels[$c->category] ?? $c->category }}</td>
          <td class="small">{{ $c->jobRequisition?->title ?? '—' }}</td>
          <td class="text-right">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
          <td>
            <form method="POST" action="{{ route('recruitment.costs.destroy', $c) }}" onsubmit="return confirm('Hapus catatan biaya ini?')">
              @csrf @method('DELETE')
              <button class="btn btn-xs btn-outline-danger">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada catatan biaya.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
