@extends('layouts.grain')
@section('title', $candidate->id ? 'Edit Kandidat' : 'Kandidat Baru')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('recruitment.candidates.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-4">{{ $candidate->id ? 'Edit Kandidat' : 'Kandidat Baru' }}</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $candidate->id ? route('recruitment.candidates.update', $candidate) : route('recruitment.candidates.store') }}">
      @if($candidate->id) @method('PUT') @endif
      @csrf

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Nama <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name', $candidate->name) }}" required>
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-4">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="{{ old('email', $candidate->email) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Telepon</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone', $candidate->phone) }}">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Job Requisition</label>
          <select name="job_requisition_id" class="form-control">
            <option value="">-- Walk-in / Tidak terkait requisition --</option>
            @foreach($requisitions as $r)
              <option value="{{ $r->id }}" @selected(old('job_requisition_id', $candidate->job_requisition_id) == $r->id)>{{ $r->title }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Sumber</label>
          <input type="text" name="source" class="form-control" placeholder="mis. Referral, Job Portal"
                 value="{{ old('source', $candidate->source) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Ekspektasi Gaji</label>
          <input type="text" data-rupiah name="expected_salary" class="form-control @error('expected_salary') is-invalid @enderror"
                 value="{{ old('expected_salary', $candidate->expected_salary) }}" placeholder="Rp">
          @error('expected_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-group">
        <label>Catatan</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $candidate->notes) }}</textarea>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('recruitment.candidates.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $candidate->id ? 'Simpan Perubahan' : 'Simpan' }}</button>
      </div>
    </form>
  </div>
</div>
@endsection
