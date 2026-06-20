@extends('layouts.grain')
@section('title', 'Kelola Saldo Medical')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Kelola Saldo Medical</div>
  <a href="{{ route('reimbursement.admin.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
</div>

{{-- Year selector --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-inline mb-0">
      <label class="font-weight-bold mr-2">Tahun:</label>
      <select name="year" class="form-control form-control-sm mr-2">
        @for($y = now()->year + 1; $y >= 2024; $y--)
          <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>
      <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
    </form>
  </div>
</div>

<form method="POST" action="{{ route('reimbursement.admin.balances.upsert') }}">
@csrf
<input type="hidden" name="year" value="{{ $year }}">

<div class="card">
  <div class="card-header font-weight-bold">Saldo Medical Tahun {{ $year }}</div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="thead-light">
        <tr>
          <th>Nama Karyawan</th>
          <th>Email</th>
          <th class="text-right" style="width:180px">Saldo Awal (Rp)</th>
          <th class="text-right" style="width:160px">Terpakai</th>
          <th class="text-right" style="width:160px">Sisa</th>
        </tr>
      </thead>
      <tbody>
      @foreach($users as $u)
        @php $bal = $balances[$u->id] ?? null; @endphp
        <tr>
          <td class="font-weight-bold">{{ $u->name }}</td>
          <td class="text-muted small">{{ $u->email }}</td>
          <td class="text-right">
            <input type="hidden" name="balances[{{ $loop->index }}][user_id]" value="{{ $u->id }}">
            <input type="number" name="balances[{{ $loop->index }}][initial_balance]"
                   class="form-control form-control-sm text-right"
                   value="{{ $bal?->initial_balance ?? 0 }}" min="0" step="1000" required>
          </td>
          <td class="text-right text-danger">
            {{ $bal ? 'Rp ' . number_format($bal->used_balance, 0, ',', '.') : '-' }}
          </td>
          <td class="text-right {{ $bal && $bal->remaining_balance > 0 ? 'text-success' : 'text-muted' }} font-weight-bold">
            {{ $bal ? 'Rp ' . number_format($bal->remaining_balance, 0, ',', '.') : '-' }}
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <button type="submit" class="btn btn-primary">
      <i class="gd-save mr-1"></i> Simpan Semua Saldo
    </button>
    <small class="text-muted ml-2">Perubahan saldo tidak mempengaruhi pengajuan yang sudah disetujui.</small>
  </div>
</div>
</form>
@endsection
