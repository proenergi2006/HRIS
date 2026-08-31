@extends('layouts.grain')
@section('title', $titlePlural)

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Master Data</li>
        <li class="breadcrumb-item active">{{ $titlePlural }}</li>
      </ol>
    </nav>

    <div class="h3 mb-4">{{ $titlePlural }}</div>

    {{-- Form tambah --}}
    <form method="POST" action="{{ route($routeBase . '.store') }}" class="mb-4">
      @csrf
      <div class="form-row align-items-end">
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Kode</label>
          <input type="text" name="code" class="form-control" placeholder="otomatis" maxlength="20" value="{{ old('code') }}">
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Nama <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
        </div>
        @foreach($extraColumns as $field => $meta)
          <div class="form-group col-6 col-md-2 mb-2">
            <label class="small text-muted mb-1">{{ $meta['label'] }}</label>
            <input type="text" name="{{ $field }}" class="form-control" value="{{ old($field) }}">
          </div>
        @endforeach
        @if($hasSortOrder)
          <div class="form-group col-4 col-md-1 mb-2">
            <label class="small text-muted mb-1">Urutan</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order') }}">
          </div>
        @endif
        <div class="form-group col-auto mb-2">
          <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
      </div>
    </form>

    @if($rows->isEmpty())
      <p class="text-muted small">Belum ada data. Tambahkan di atas.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:120px">Kode</th>
              <th>Nama</th>
              @foreach($extraColumns as $meta)
                <th style="width:{{ $meta['width'] ?? '140px' }}">{{ $meta['label'] }}</th>
              @endforeach
              @if($hasSortOrder)<th class="text-center" style="width:90px">Urutan</th>@endif
              <th class="text-center" style="width:70px">Aktif</th>
              <th style="width:80px"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($rows as $row)
            @php $f = 'row-' . $row->id; @endphp
            <tr>
              <td><input type="text" name="code" form="{{ $f }}" value="{{ old('code', $row->code) }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="name" form="{{ $f }}" value="{{ old('name', $row->name) }}" class="form-control form-control-sm"></td>
              @foreach($extraColumns as $field => $meta)
                <td><input type="text" name="{{ $field }}" form="{{ $f }}" value="{{ old($field, $row->$field) }}" class="form-control form-control-sm"></td>
              @endforeach
              @if($hasSortOrder)
                <td><input type="number" name="sort_order" form="{{ $f }}" value="{{ old('sort_order', $row->sort_order) }}" class="form-control form-control-sm text-center"></td>
              @endif
              <td class="text-center align-middle">
                <div class="custom-control custom-switch">
                  <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                  <input type="checkbox" class="custom-control-input" id="act-{{ $row->id }}" form="{{ $f }}" name="is_active" value="1" {{ $row->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label" for="act-{{ $row->id }}"></label>
                </div>
              </td>
              <td class="align-middle text-nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning" title="Simpan"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger"
                   data-confirm="Hapus &ldquo;{{ $row->name }}&rdquo;?"
                   data-confirm-title="Hapus {{ $titlePlural }}"
                   data-form="del-{{ $row->id }}"><i class="gd-trash"></i></a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($rows as $row)
        <form id="row-{{ $row->id }}" method="POST" action="{{ route($routeBase . '.update', $row) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="del-{{ $row->id }}" method="POST" action="{{ route($routeBase . '.destroy', $row) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
