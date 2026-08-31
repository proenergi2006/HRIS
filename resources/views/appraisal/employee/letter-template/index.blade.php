@extends('layouts.grain')
@section('title', 'Template Surat')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('appraisal.employee-letters.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Surat</a>
</div>

<div class="h3 mb-4">Template Surat</div>

<div class="alert alert-info py-2 px-3" style="font-size:.85rem">
  <i class="gd-info mr-1"></i> Placeholder yang bisa dipakai di isi surat:
  @foreach(\App\Models\LetterTemplate::$placeholders as $ph => $label)
    <code>{{ $ph }}</code>{{ !$loop->last ? ',' : '' }}
  @endforeach
</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Template Baru</div>
  <div class="card-body">
    <form method="POST" action="{{ route('appraisal.letter-templates.store') }}">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-5 mb-2">
          <input type="text" name="title" class="form-control" placeholder="Judul (mis. Surat Keterangan Kerja)" required>
        </div>
        <div class="form-group col-md-3 mb-2">
          <select name="category" class="form-control" required>
            @foreach(\App\Models\LetterTemplate::$categoryLabels as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-4 mb-2">
          <select name="company_id" class="form-control">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="form-group">
        <textarea name="body" rows="6" class="form-control" placeholder="Isi surat, pakai placeholder @{{nama}}, @{{jabatan}}, dst." required></textarea>
      </div>
      <div class="custom-control custom-checkbox mb-2">
        <input type="checkbox" class="custom-control-input" id="ss-new" name="self_service" value="1">
        <label class="custom-control-label small" for="ss-new">Boleh diminta mandiri oleh karyawan (self-service)</label>
      </div>
      <button class="btn btn-primary btn-sm"><i class="gd-plus mr-1"></i> Simpan Template</button>
    </form>
  </div>
</div>

@forelse($templates as $t)
<div class="card mb-3">
  <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
    <span>{{ $t->title }} <span class="badge badge-light border ml-1">{{ \App\Models\LetterTemplate::$categoryLabels[$t->category] ?? $t->category }}</span></span>
    <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus template {{ $t->title }}?" data-confirm-title="Hapus Template" data-form="lt-del-{{ $t->id }}"><i class="gd-trash"></i></a>
    <form id="lt-del-{{ $t->id }}" method="POST" action="{{ route('appraisal.letter-templates.destroy', $t) }}" class="d-none">@csrf @method('DELETE')</form>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('appraisal.letter-templates.update', $t) }}">
      @csrf @method('PUT')
      <div class="form-row">
        <div class="form-group col-md-5 mb-2"><input type="text" name="title" value="{{ $t->title }}" class="form-control form-control-sm" required></div>
        <div class="form-group col-md-3 mb-2">
          <select name="category" class="form-control form-control-sm" required>
            @foreach(\App\Models\LetterTemplate::$categoryLabels as $key => $label)<option value="{{ $key }}" @selected($t->category === $key)>{{ $label }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-3 mb-2">
          <select name="company_id" class="form-control form-control-sm">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected($t->company_id === $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-1 mb-2 text-center">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }} title="Aktif">
        </div>
      </div>
      <div class="form-group">
        <textarea name="body" rows="5" class="form-control form-control-sm">{{ $t->body }}</textarea>
      </div>
      <div class="custom-control custom-checkbox mb-2">
        <input type="checkbox" class="custom-control-input" id="ss-{{ $t->id }}" name="self_service" value="1" @checked($t->self_service)>
        <label class="custom-control-label small" for="ss-{{ $t->id }}">Boleh diminta mandiri oleh karyawan (self-service)</label>
      </div>
      <button class="btn btn-sm btn-outline-warning"><i class="gd-pencil mr-1"></i> Simpan Perubahan</button>
    </form>
  </div>
</div>
@empty
  <p class="text-muted small">Belum ada template surat.</p>
@endforelse
@endsection
