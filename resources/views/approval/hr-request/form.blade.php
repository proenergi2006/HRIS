@extends('layouts.grain')
@section('title', ($row->exists ? 'Edit ' : 'Buat ') . $cfg['label'])

@section('content')
@include('components.notification')

@php
    $isNew  = ! $row->exists;
    $action = $isNew ? route('approval.hr-request.store', $kind) : route('approval.hr-request.update', [$kind, $row->id]);
    $val    = fn ($f, $d = null) => old($f, $row->$f ?? $d);
@endphp

<div class="mb-3"><a href="{{ route('approval.hr-request.index', $kind) }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">{{ $isNew ? 'Buat' : 'Edit' }} Pengajuan {{ $cfg['label'] }}</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $action }}">
      @csrf
      @unless($isNew) @method('PUT') @endunless

      <div class="form-group">
        <label>Karyawan <span class="text-danger">*</span></label>
        <select name="employee_id" class="form-control" required {{ $row->exists && $row->status !== 'draft' ? 'disabled' : '' }}>
          <option value="">— pilih —</option>
          @foreach($employees as $e)<option value="{{ $e->id }}" @selected($val('employee_id') == $e->id)>{{ $e->name }}</option>@endforeach
        </select>
      </div>

      @if($kind === 'reward')
        <div class="form-row">
          <div class="form-group col-md-6"><label>Jenis Reward <span class="text-danger">*</span></label><input type="text" name="reward_type" class="form-control" value="{{ $val('reward_type') }}" required placeholder="Bonus, Penghargaan, Kenaikan Grade…"></div>
          <div class="form-group col-md-3"><label>Tanggal Efektif</label><input type="date" name="effective_date" class="form-control" value="{{ optional($row->effective_date)->format('Y-m-d') ?? old('effective_date') }}"></div>
          <div class="form-group col-md-3"><label>Nominal (Rp)</label><input type="number" data-rupiah name="amount" class="form-control" min="0" step="1000" value="{{ $val('amount') }}"></div>
        </div>
        <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3">{{ $val('description') }}</textarea></div>

      @elseif($kind === 'punishment')
        <div class="form-row">
          <div class="form-group col-md-6"><label>Jenis Pelanggaran <span class="text-danger">*</span></label><input type="text" name="violation_type" class="form-control" value="{{ $val('violation_type') }}" required></div>
          <div class="form-group col-md-3"><label>Tingkat Sanksi <span class="text-danger">*</span></label>
            <select name="sanction_level" class="form-control" required>@foreach($sanctionLabels as $v => $l)<option value="{{ $v }}" @selected($val('sanction_level','sp1') === $v)>{{ $l }}</option>@endforeach</select>
          </div>
          <div class="form-group col-md-3"><label>Tanggal Kejadian</label><input type="date" name="incident_date" class="form-control" value="{{ optional($row->incident_date)->format('Y-m-d') ?? old('incident_date') }}"></div>
        </div>
        <div class="form-group"><label>Tanggal Efektif</label><input type="date" name="effective_date" class="form-control" value="{{ optional($row->effective_date)->format('Y-m-d') ?? old('effective_date') }}"></div>
        <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3">{{ $val('description') }}</textarea></div>

      @elseif($kind === 'promotion-rotation')
        <div class="form-row">
          <div class="form-group col-md-4"><label>Jenis <span class="text-danger">*</span></label>
            <select name="request_type" class="form-control" required>@foreach($promoTypes as $v => $l)<option value="{{ $v }}" @selected($val('request_type','promotion') === $v)>{{ $l }}</option>@endforeach</select>
          </div>
          <div class="form-group col-md-4"><label>Jabatan Asal</label>
            <select name="from_position_id" class="form-control"><option value="">—</option>@foreach($positions as $p)<option value="{{ $p->id }}" @selected($val('from_position_id') == $p->id)>{{ $p->name }}</option>@endforeach</select>
          </div>
          <div class="form-group col-md-4"><label>Jabatan Tujuan</label>
            <select name="to_position_id" class="form-control"><option value="">—</option>@foreach($positions as $p)<option value="{{ $p->id }}" @selected($val('to_position_id') == $p->id)>{{ $p->name }}</option>@endforeach</select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-4"><label>Perusahaan Tujuan (jika pindah)</label>
            <select name="to_company_id" class="form-control"><option value="">—</option>@foreach($companies as $c)<option value="{{ $c->id }}" @selected($val('to_company_id') == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach</select>
          </div>
          <div class="form-group col-md-4"><label>Tanggal Efektif</label><input type="date" name="effective_date" class="form-control" value="{{ optional($row->effective_date)->format('Y-m-d') ?? old('effective_date') }}"></div>
        </div>
        <div class="form-group"><label>Alasan</label><textarea name="reason" class="form-control" rows="3">{{ $val('reason') }}</textarea></div>

      @elseif($kind === 'termination')
        <div class="form-row">
          <div class="form-group col-md-4"><label>Jenis <span class="text-danger">*</span></label>
            <select name="termination_type" class="form-control" required>@foreach($termTypes as $v => $l)<option value="{{ $v }}" @selected($val('termination_type','resign') === $v)>{{ $l }}</option>@endforeach</select>
          </div>
          <div class="form-group col-md-4"><label>Hari Kerja Terakhir</label><input type="date" name="last_working_date" class="form-control" value="{{ optional($row->last_working_date)->format('Y-m-d') ?? old('last_working_date') }}"></div>
          <div class="form-group col-md-4"><label>Tanggal Efektif</label><input type="date" name="effective_date" class="form-control" value="{{ optional($row->effective_date)->format('Y-m-d') ?? old('effective_date') }}"></div>
        </div>
        <div class="form-group"><label>Alasan</label><textarea name="reason" class="form-control" rows="3">{{ $val('reason') }}</textarea></div>
      @endif

      <button type="submit" class="btn btn-primary">{{ $isNew ? 'Simpan Draft' : 'Perbarui' }}</button>

      @if($row->exists && $row->status === 'draft')
        <button form="submit-form" type="submit" class="btn btn-success float-right">Ajukan untuk Persetujuan</button>
      @endif
    </form>

    @if($row->exists && $row->status === 'draft')
      <form id="submit-form" method="POST" action="{{ route('approval.hr-request.submit', [$kind, $row->id]) }}" class="d-none">@csrf</form>
    @endif
  </div>
</div>
@endsection
