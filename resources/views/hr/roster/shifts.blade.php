@extends('layouts.grain')
@section('title', 'Master Shift')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.roster.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Roster</a></div>
<div class="h3 mb-4">Master Shift</div>

<div class="card mb-3">
  <div class="card-header py-2"><strong class="small">Tambah Shift</strong></div>
  <div class="card-body">
    <form method="POST" action="{{ route('hr.roster.shifts.store') }}" class="form-row align-items-end">
      @csrf
      <div class="form-group col-md-2"><label class="small">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm">
          <option value="">Semua PT</option>
          @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-1"><label class="small">Kode</label><input name="code" class="form-control form-control-sm" required></div>
      <div class="form-group col-md-3"><label class="small">Nama</label><input name="name" class="form-control form-control-sm" required></div>
      <div class="form-group col-md-1"><label class="small">Masuk</label><input type="time" name="start_time" class="form-control form-control-sm" required></div>
      <div class="form-group col-md-1"><label class="small">Pulang</label><input type="time" name="end_time" class="form-control form-control-sm" required></div>
      <div class="form-group col-md-1"><label class="small">Toleransi (mnt)</label><input type="number" name="late_grace_minutes" class="form-control form-control-sm" value="0"></div>
      <div class="form-group col-md-1"><label class="small">Istirahat</label><input type="number" name="break_minutes" class="form-control form-control-sm" value="60"></div>
      <div class="form-group col-md-1">
        <div class="custom-control custom-checkbox mt-3"><input type="checkbox" class="custom-control-input" id="cm" name="crosses_midnight" value="1"><label class="custom-control-label small" for="cm">Lewat 00:00</label></div>
      </div>
      <div class="form-group col-md-12"><button class="btn btn-primary btn-sm">Tambah</button></div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="thead-light"><tr><th>Daftar Shift</th></tr></thead>
      <tbody>
      @foreach($shifts as $s)
        <tr>
          <td class="p-2">
            <form id="shift-form-{{ $s->id }}" method="POST" action="{{ route('hr.roster.shifts.update', $s) }}" class="form-row align-items-end mb-0">
              @csrf @method('PUT')
              <input type="hidden" name="company_id" value="{{ $s->company_id }}">
              <div class="col-md-1"><label class="small mb-0">Kode</label><input name="code" class="form-control form-control-sm" value="{{ $s->code }}"></div>
              <div class="col-md-3"><label class="small mb-0">Nama</label><input name="name" class="form-control form-control-sm" value="{{ $s->name }}"></div>
              <div class="col-md-2"><label class="small mb-0">Perusahaan</label><div class="small pt-1">{{ $s->company?->short_name ?? 'Semua PT' }}</div></div>
              <div class="col-md-1"><label class="small mb-0">Masuk</label><input type="time" name="start_time" class="form-control form-control-sm" value="{{ \Illuminate\Support\Str::substr($s->start_time,0,5) }}"></div>
              <div class="col-md-1"><label class="small mb-0">Pulang</label><input type="time" name="end_time" class="form-control form-control-sm" value="{{ \Illuminate\Support\Str::substr($s->end_time,0,5) }}"></div>
              <div class="col-md-1"><label class="small mb-0">Toleransi</label><input type="number" name="late_grace_minutes" class="form-control form-control-sm" value="{{ $s->late_grace_minutes }}"></div>
              <div class="col-md-1"><label class="small mb-0">Istirahat</label><input type="number" name="break_minutes" class="form-control form-control-sm" value="{{ $s->break_minutes }}"></div>
              <div class="col-md-2">
                <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="cm{{ $s->id }}" name="crosses_midnight" value="1" @checked($s->crosses_midnight)><label class="custom-control-label small" for="cm{{ $s->id }}">Lewat 00:00</label></div>
                <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="ia{{ $s->id }}" name="is_active" value="1" @checked($s->is_active)><label class="custom-control-label small" for="ia{{ $s->id }}">Aktif</label></div>
              </div>
            </form>
            <div class="d-flex mt-2" style="gap:.5rem">
              <button type="submit" form="shift-form-{{ $s->id }}" class="btn btn-xs btn-outline-primary">Simpan</button>
              <form method="POST" action="{{ route('hr.roster.shifts.destroy', $s) }}" onsubmit="return confirm('Hapus shift {{ $s->code }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-outline-danger">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
