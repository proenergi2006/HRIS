@extends('layouts.grain')
@section('title', 'Terbitkan Surat')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.employee-letters.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-4">Terbitkan Surat</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">1. Pilih Karyawan &amp; Template</div>
  <div class="card-body">
    <form method="POST" action="{{ route('appraisal.employee-letters.preview') }}" class="form-row align-items-end">
      @csrf
      <input type="hidden" name="letter_request_id" value="{{ $letterRequestId ?? '' }}">
      <div class="form-group col-md-5 mb-2">
        <label class="small font-weight-bold mb-1">Karyawan</label>
        <select name="employee_id" class="form-control" required>
          <option value="">-- Pilih --</option>
          @foreach($employees as $e)
            <option value="{{ $e->id }}" @selected($selectedEmployee?->id === $e->id)>{{ $e->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-5 mb-2">
        <label class="small font-weight-bold mb-1">Template</label>
        <select name="letter_template_id" class="form-control" required>
          <option value="">-- Pilih --</option>
          @foreach($templates as $t)
            <option value="{{ $t->id }}" @selected(($previewTemplate ?? null)?->id === $t->id)>{{ $t->title }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-2">
        <button type="submit" class="btn btn-outline-primary btn-block">Preview</button>
      </div>
    </form>
  </div>
</div>

@if(!empty($preview))
<div class="card">
  <div class="card-header font-weight-bold">2. Terbitkan</div>
  <div class="card-body">
    @if(!empty($letterRequestId))
      <div class="alert alert-info py-2 px-3 small">Diterbitkan dari permintaan surat karyawan #{{ $letterRequestId }}.</div>
    @endif
    <form method="POST" action="{{ route('appraisal.employee-letters.store') }}">
      @csrf
      <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">
      <input type="hidden" name="letter_template_id" value="{{ $previewTemplate->id }}">
      <input type="hidden" name="letter_request_id" value="{{ $letterRequestId ?? '' }}">

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Judul Surat</label>
          <input type="text" name="title" class="form-control" value="{{ $previewTemplate->title }}" required>
        </div>
        <div class="form-group col-md-3">
          <label>Kategori</label>
          <select name="category" class="form-control" required>
            @foreach(\App\Models\LetterTemplate::$categoryLabels as $key => $label)
              <option value="{{ $key }}" @selected($previewTemplate->category === $key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Tanggal Terbit</label>
          <input type="date" name="issued_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
        </div>
      </div>

      <div class="form-group">
        <label>Isi Surat (bisa disunting sebelum diterbitkan)</label>
        <textarea name="body" rows="10" class="form-control">{{ $preview }}</textarea>
      </div>

      <button type="submit" class="btn btn-success">
        <i class="gd-check mr-1"></i> Terbitkan Surat
      </button>
    </form>
  </div>
</div>
@endif
@endsection
