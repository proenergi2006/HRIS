@extends('layouts.grain')
@section('title', 'Kebijakan Cuti')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.leave.balances') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Saldo Cuti</a></div>
<div class="h3 mb-3">Kebijakan Cuti (Carry-Forward &amp; Kuota per Golongan)</div>

<div class="alert alert-info py-2 px-3" style="font-size:.85rem">
  <i class="gd-info mr-1"></i> Jenis cuti yang punya kebijakan di sini akan pakai kuota &amp; carry-forward
  di bawah, bukan standar jenis cuti. Kosongkan Perusahaan/Level untuk berlaku umum. Jalankan
  <strong>leave:year-end</strong> tiap awal tahun (terjadwal 1 Januari 02:00) untuk alokasi otomatis.
</div>

<div class="card mb-3">
  <div class="card-header py-2"><strong class="small">Tambah Kebijakan</strong></div>
  <div class="card-body">
    <form method="POST" action="{{ route('hr.leave.policies.store') }}" class="form-row align-items-end">
      @csrf
      <div class="form-group col-md-2"><label class="small">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm"><option value="">Semua PT</option>@foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach</select>
      </div>
      <div class="form-group col-md-2"><label class="small">Jenis Cuti</label>
        <select name="leave_type_id" class="form-control form-control-sm" required>@foreach($leaveTypes as $lt)<option value="{{ $lt->id }}">{{ $lt->name }}</option>@endforeach</select>
      </div>
      <div class="form-group col-md-2"><label class="small">Level</label>
        <select name="level_id" class="form-control form-control-sm"><option value="">Semua Level</option>@foreach($levels as $lv)<option value="{{ $lv->id }}">{{ $lv->name }}</option>@endforeach</select>
      </div>
      <div class="form-group col-md-1"><label class="small">Min. Th Kerja</label><input type="number" name="min_years_service" class="form-control form-control-sm" value="0" min="0"></div>
      <div class="form-group col-md-1"><label class="small">Kuota (hr)</label><input type="number" name="quota_days" step="0.5" class="form-control form-control-sm" required></div>
      <div class="form-group col-md-1"><label class="small">Carry Maks</label><input type="number" name="carry_forward_max_days" step="0.5" class="form-control form-control-sm" value="0"></div>
      <div class="form-group col-md-2"><label class="small">Hangus Bulan Ke-</label><input type="number" name="carry_forward_expire_month" min="1" max="12" class="form-control form-control-sm" value="3" required></div>
      <div class="form-group col-md-1"><button class="btn btn-primary btn-sm">Tambah</button></div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="thead-light"><tr><th>Jenis Cuti</th><th>Perusahaan</th><th>Level</th><th class="text-center">Min Th</th><th class="text-center">Kuota</th><th class="text-center">Carry Maks</th><th class="text-center">Hangus Bln</th><th class="text-center">Aktif</th><th></th></tr></thead>
      <tbody>
      @forelse($policies as $p)
        <tr>
          <td>{{ $p->leaveType?->name }}</td>
          <td class="small">{{ $p->company?->short_name ?? 'Semua PT' }}</td>
          <td class="small">{{ $p->level?->name ?? 'Semua Level' }}</td>
          <td class="text-center">{{ $p->min_years_service }}</td>
          <td class="text-center">{{ $p->quota_days }}</td>
          <td class="text-center">{{ $p->carry_forward_max_days }}</td>
          <td class="text-center">{{ $p->carry_forward_expire_month }}</td>
          <td class="text-center">{{ $p->is_active ? 'Ya' : 'Tidak' }}</td>
          <td>
            <form method="POST" action="{{ route('hr.leave.policies.destroy', $p) }}" onsubmit="return confirm('Hapus kebijakan ini?')">
              @csrf @method('DELETE')
              <button class="btn btn-xs btn-outline-danger">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="9" class="text-center text-muted py-3">Belum ada kebijakan cuti.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
