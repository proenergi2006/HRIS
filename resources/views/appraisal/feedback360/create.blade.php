@extends('layouts.grain')
@section('title', 'Cycle 360° Baru')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.feedback360.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">Cycle 360° Baru</div>

<div class="card" style="max-width:640px">
  <div class="card-body">
    <form method="POST" action="{{ route('appraisal.feedback360.store') }}">
      @csrf
      <div class="form-group">
        <label>Judul <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" placeholder="mis. 360° Feedback Semester 1 2027" required>
      </div>
      <div class="form-group">
        <label>Perusahaan <span class="text-danger">*</span></label>
        <select name="company_id" class="form-control" required>
          @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Mulai <span class="text-danger">*</span></label>
          <input type="date" name="period_start" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
          <label>Selesai <span class="text-danger">*</span></label>
          <input type="date" name="period_end" class="form-control" required>
        </div>
      </div>
      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('appraisal.feedback360.index') }}" class="btn btn-secondary">Batal</a>
        <button class="btn btn-primary">Buat Cycle</button>
      </div>
    </form>
  </div>
</div>
@endsection
