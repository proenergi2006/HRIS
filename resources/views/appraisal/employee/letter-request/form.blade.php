@extends('layouts.grain')
@section('title', 'Ajukan Permintaan Surat')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.letter-requests.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">Ajukan Permintaan Surat</div>

<div class="card">
  <div class="card-body">
    @if($templates->isEmpty())
      <p class="text-muted small mb-0">Belum ada jenis surat yang tersedia untuk permintaan mandiri. Hubungi HR.</p>
    @else
      <form method="POST" action="{{ route('appraisal.letter-requests.store') }}">
        @csrf
        <div class="form-group">
          <label>Jenis Surat <span class="text-danger">*</span></label>
          <select name="letter_template_id" class="form-control @error('letter_template_id') is-invalid @enderror" required>
            <option value="">— pilih —</option>
            @foreach($templates as $t)<option value="{{ $t->id }}" @selected(old('letter_template_id') == $t->id)>{{ $t->title }}</option>@endforeach
          </select>
          @error('letter_template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label>Keperluan <span class="text-danger">*</span></label>
          <select name="purpose" class="form-control @error('purpose') is-invalid @enderror" required>
            @foreach(\App\Models\LetterRequest::$purposeLabels as $k => $v)<option value="{{ $k }}" @selected(old('purpose') === $k)>{{ $v }}</option>@endforeach
          </select>
          @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label>Catatan</label>
          <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
        </div>
        <div class="d-flex justify-content-between">
          <a href="{{ route('appraisal.letter-requests.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
        </div>
      </form>
    @endif
  </div>
</div>
@endsection
