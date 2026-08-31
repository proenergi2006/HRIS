@extends('layouts.grain')
@section('title', 'Kamus Kompetensi')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Competency Framework</div>
</div>

<ul class="nav nav-pills mb-4">
  <li class="nav-item"><a class="nav-link active" href="{{ route('competency.dictionary.index') }}">Kamus Kompetensi</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.positions.index') }}">Profil Jabatan</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.assessments.index') }}">Penilaian</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.gap') }}">Analisis Gap</a></li>
</ul>

<div class="card">
  <div class="card-header font-weight-bold">Daftar Kompetensi</div>
  <div class="card-body">

    <p class="text-muted small">
      Skala level: @foreach(\App\Models\Competency\Competency::$levelLabels as $n => $l)<strong>{{ $n }}</strong> {{ $l }}@if(!$loop->last) &nbsp;·&nbsp; @endif @endforeach
    </p>

    <form method="POST" action="{{ route('competency.dictionary.store') }}" class="mb-4">
      @csrf
      <div class="form-row align-items-end">
        <div class="form-group col-6 col-md-2 mb-2">
          <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="Kode *" required maxlength="30" value="{{ old('code') }}">
          @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <input type="text" name="name" class="form-control" placeholder="Nama kompetensi *" required maxlength="150" value="{{ old('name') }}">
        </div>
        <div class="form-group col-6 col-md-2 mb-2">
          <input type="text" name="category" class="form-control" placeholder="Kategori" maxlength="50" value="{{ old('category') }}">
        </div>
        <div class="form-group col-6 col-md-2 mb-2">
          <select name="company_id" class="form-control">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-auto mb-2">
          <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
        <div class="form-group col-12 mb-0">
          <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi (opsional)">{{ old('description') }}</textarea>
        </div>
      </div>
    </form>

    @if($competencies->isEmpty())
      <p class="text-muted small mb-0">Belum ada kompetensi.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th style="width:110px">Kode</th><th>Nama</th><th style="width:130px">Kategori</th><th style="width:130px">PT</th><th class="text-center" style="width:60px">Jbtn</th><th class="text-center" style="width:60px">Kry</th><th class="text-center" style="width:60px">Aktif</th><th style="width:80px"></th></tr>
          </thead>
          <tbody>
          @foreach($competencies as $c)
            @php $f = 'cmp-form-' . $c->id; @endphp
            <tr>
              <td><input type="text" name="code" form="{{ $f }}" value="{{ $c->code }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="name" form="{{ $f }}" value="{{ $c->name }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="category" form="{{ $f }}" value="{{ $c->category }}" class="form-control form-control-sm"></td>
              <td>
                <select name="company_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">Semua</option>
                  @foreach($companies as $co)<option value="{{ $co->id }}" @selected($c->company_id == $co->id)>{{ $co->short_name ?? $co->name }}</option>@endforeach
                </select>
              </td>
              <td class="text-center align-middle">{{ $c->position_competencies_count }}</td>
              <td class="text-center align-middle">{{ $c->employee_competencies_count }}</td>
              <td class="text-center align-middle">
                <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                <input type="checkbox" name="is_active" form="{{ $f }}" value="1" {{ $c->is_active ? 'checked' : '' }}>
              </td>
              <td class="align-middle text-nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger"
                   data-confirm="Hapus kompetensi {{ $c->name }}?" data-confirm-title="Hapus Kompetensi" data-form="cmp-del-{{ $c->id }}"
                   @if($c->position_competencies_count || $c->employee_competencies_count) style="pointer-events:none;opacity:.4" title="Masih dipakai" @endif>
                  <i class="gd-trash"></i>
                </a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($competencies as $c)
        <form id="cmp-form-{{ $c->id }}" method="POST" action="{{ route('competency.dictionary.update', $c) }}" class="d-none">@csrf @method('PUT')<input type="hidden" name="description" value="{{ $c->description }}"></form>
        <form id="cmp-del-{{ $c->id }}" method="POST" action="{{ route('competency.dictionary.destroy', $c) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
