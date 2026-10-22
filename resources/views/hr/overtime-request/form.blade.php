@extends('layouts.grain')
@section('title', 'Ajukan Lembur')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.overtime-requests.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-4">Ajukan Lembur — {{ $employee->name }}</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('hr.overtime-requests.store') }}">
      @csrf

      @if($onBehalfOptions->isNotEmpty())
        <div class="form-group">
          <label>Atas nama karyawan</label>
          <select name="on_behalf_employee_id" class="form-control"
                  onchange="window.location='{{ route('hr.overtime-requests.create') }}?on_behalf_employee_id=' + this.value">
            @foreach($onBehalfOptions as $opt)
              <option value="{{ $opt->id }}" @selected($opt->id === $employee->id)>{{ $opt->name }}{{ $opt->nip ? ' — ' . $opt->nip : '' }}</option>
            @endforeach
          </select>
          <small class="text-muted">Admin HR dapat mengajukan lembur untuk karyawan lain.</small>
        </div>
      @endif

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Tanggal <span class="text-danger">*</span></label>
          <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', now()->format('Y-m-d')) }}" required>
          @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-4">
          <label>Jumlah Jam <span class="text-danger">*</span></label>
          <input type="number" step="0.5" min="0.5" max="24" name="planned_hours" class="form-control @error('planned_hours') is-invalid @enderror" value="{{ old('planned_hours') }}" required>
          @error('planned_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-group">
        <label>Alasan</label>
        <textarea name="reason" rows="3" class="form-control">{{ old('reason') }}</textarea>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('hr.overtime-requests.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan Draft</button>
      </div>
    </form>
  </div>
</div>
@endsection
