@extends('layouts.grain')
@section('title', 'Saldo Cuti Karyawan')

@section('content')
@include('components.notification')

@php $activeTypes = $leaveTypes->where('days_per_year', '>', 0)->values(); @endphp

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.leave.index') }}">Manajemen Cuti</a></li>
    <li class="breadcrumb-item active">Saldo Cuti</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Saldo Cuti Karyawan</div>
  <a href="{{ route('hr.leave.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

{{-- Filter --}}
<div class="card mb-3 shadow-sm">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold text-muted">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $companyId == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold text-muted">Tahun</label>
        <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach(range(now()->year + 1, now()->year - 2) as $y)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

{{-- Tabel saldo --}}
<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:.88rem">
        <thead class="thead-light">
          <tr>
            <th style="min-width:180px">Karyawan</th>
            @foreach($activeTypes as $lt)
              <th class="text-center" style="min-width:110px">
                {{ $lt->name }}<br>
                <small class="font-weight-normal text-muted">Std: {{ $lt->days_per_year }} hr/th</small>
              </th>
            @endforeach
            <th style="width:90px"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($employees as $emp)
          @php $balByType = $emp->leaveBalances->keyBy('leave_type_id'); @endphp
          <tr>
            <td class="align-middle">
              <div class="font-weight-bold">{{ $emp->name }}</div>
              <small class="text-muted">{{ $emp->nip ?? '—' }}</small>
            </td>
            @foreach($activeTypes as $lt)
              @php $bal = $balByType[$lt->id] ?? null; @endphp
              <td class="text-center align-middle">
                @if($bal)
                  @php $sisa = $bal->getRemaining(); @endphp
                  <span class="font-weight-bold {{ $sisa <= 0 ? 'text-danger' : ($sisa <= 3 ? 'text-warning' : 'text-success') }}"
                        style="font-size:1rem">{{ $sisa }}</span>
                  <div class="text-muted" style="font-size:.75rem">dari {{ $bal->allocated }} hr</div>
                  @if($bal->used > 0)
                    <div style="font-size:.7rem;color:#aaa">terpakai {{ $bal->used }}</div>
                  @endif
                @else
                  <span class="text-muted">—</span>
                  <div style="font-size:.72rem;color:#bbb">belum diset</div>
                @endif
              </td>
            @endforeach
            <td class="align-middle text-center">
              <button type="button" class="btn btn-sm btn-outline-primary"
                      onclick="openSiproModal('saldo-modal-{{ $emp->id }}')">
                <i class="gd-pencil"></i> Edit
              </button>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="{{ $activeTypes->count() + 2 }}" class="text-center text-muted py-4">
              Belum ada karyawan aktif untuk perusahaan ini.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

{{-- ===============================================================
     Push modals ke @stack('modals') di layout (body level),
     pakai sistem sipro-overlay bukan Bootstrap modal.
     =============================================================== --}}
@push('modals')
@php $activeTypes = $leaveTypes->where('days_per_year', '>', 0)->values(); @endphp
@foreach($employees as $emp)
@php $balByType = $emp->leaveBalances->keyBy('leave_type_id'); @endphp

<div class="sipro-overlay" id="saldo-modal-{{ $emp->id }}">
  <div class="sipro-backdrop" onclick="closeSiproModal('saldo-modal-{{ $emp->id }}')"></div>
  <div class="sipro-dialog" style="max-width:480px">

    <div class="sipro-header" style="background:#0F2A4A">
      <h5 style="color:#fff;margin:0;font-size:.95rem">
        <i class="gd-calendar mr-1"></i> Edit Saldo Cuti &mdash; {{ $emp->name }}
      </h5>
      <button class="sipro-close" style="color:#fff" onclick="closeSiproModal('saldo-modal-{{ $emp->id }}')">&times;</button>
    </div>

    <div class="sipro-body" style="padding:1rem">
      <p class="text-muted small mb-3">
        Tahun <strong>{{ $year }}</strong>. Klik <em>Simpan</em> pada setiap jenis cuti yang ingin diubah.
      </p>

      @foreach($activeTypes as $lt)
      @php $bal = $balByType[$lt->id] ?? null; @endphp
      <div class="border rounded p-3 mb-3">
        <div class="mb-2">
          <div class="font-weight-bold" style="font-size:.9rem">{{ $lt->name }}</div>
          <small class="text-muted">
            Standar {{ $lt->days_per_year }} hari/tahun
            @if($bal)
              &nbsp;&bull;&nbsp; Sisa:
              <strong class="{{ $bal->getRemaining() <= 0 ? 'text-danger' : ($bal->getRemaining() <= 3 ? 'text-warning' : 'text-success') }}">
                {{ $bal->getRemaining() }}
              </strong> hari
            @endif
          </small>
        </div>
        <form method="POST" action="{{ route('hr.leave.balances.upsert') }}"
              class="d-flex align-items-end" style="gap:.5rem">
          @csrf
          <input type="hidden" name="employee_id"   value="{{ $emp->id }}">
          <input type="hidden" name="leave_type_id" value="{{ $lt->id }}">
          <input type="hidden" name="year"          value="{{ $year }}">
          <div class="flex-grow-1">
            <label class="small text-muted mb-1">Jatah (hari)</label>
            <input type="number" name="allocated" class="form-control form-control-sm"
                   value="{{ $bal ? $bal->allocated : $lt->days_per_year }}"
                   min="0" step="0.5" required>
          </div>
          <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap">
            Simpan
          </button>
        </form>
      </div>
      @endforeach
    </div>

    <div class="sipro-footer">
      <button type="button" class="btn btn-secondary btn-sm"
              onclick="closeSiproModal('saldo-modal-{{ $emp->id }}')">Tutup</button>
    </div>

  </div>
</div>
@endforeach
@endpush
