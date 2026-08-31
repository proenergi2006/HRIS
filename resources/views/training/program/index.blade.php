@extends('layouts.grain')
@section('title', 'Program Training')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('training.participants.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Peserta Training</a>
</div>

<div class="h3 mb-4">Program Training</div>

<div class="card">
  <div class="card-header font-weight-bold">Daftar Program</div>
  <div class="card-body">

    <form method="POST" action="{{ route('training.programs.store') }}" class="mb-4">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-3 mb-2">
          <input type="text" name="title" class="form-control" placeholder="Judul program" required>
        </div>
        <div class="form-group col-md-2 mb-2">
          <input type="text" name="category" class="form-control" placeholder="Kategori">
        </div>
        <div class="form-group col-md-2 mb-2">
          <input type="text" name="provider" class="form-control" placeholder="Penyelenggara">
        </div>
        <div class="form-group col-md-2 mb-2">
          <input type="number" name="duration_hours" class="form-control" placeholder="Durasi (jam)" min="0">
        </div>
        <div class="form-group col-md-2 mb-2">
          <select name="company_id" class="form-control">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-1 mb-2">
          <button type="submit" class="btn btn-primary btn-block">+</button>
        </div>
        <div class="form-group col-12 mb-0">
          <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi (opsional)"></textarea>
        </div>
      </div>
    </form>

    @if($programs->isEmpty())
      <p class="text-muted small">Belum ada program training.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Judul</th><th>Kategori</th><th>Penyelenggara</th><th class="text-center">Durasi</th><th>PT</th><th class="text-center">Peserta</th><th class="text-center">Aktif</th><th></th></tr>
          </thead>
          <tbody>
          @foreach($programs as $p)
            @php $f = 'tp-form-' . $p->id; @endphp
            <tr>
              <td><input type="text" name="title" form="{{ $f }}" value="{{ $p->title }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="category" form="{{ $f }}" value="{{ $p->category }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="provider" form="{{ $f }}" value="{{ $p->provider }}" class="form-control form-control-sm"></td>
              <td style="width:100px"><input type="number" name="duration_hours" form="{{ $f }}" value="{{ $p->duration_hours }}" class="form-control form-control-sm"></td>
              <td style="width:130px">
                <select name="company_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">Semua</option>
                  @foreach($companies as $c)<option value="{{ $c->id }}" @selected($p->company_id == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
                </select>
              </td>
              <td class="text-center align-middle">
                <a href="{{ route('training.participants.index', ['training_program_id' => $p->id]) }}">{{ $p->participants_count }}</a>
              </td>
              <td class="text-center align-middle" style="width:60px">
                <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                <input type="checkbox" name="is_active" form="{{ $f }}" value="1" {{ $p->is_active ? 'checked' : '' }}>
              </td>
              <td style="width:80px;white-space:nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus program {{ $p->title }}?" data-confirm-title="Hapus Program" data-form="tp-del-{{ $p->id }}"><i class="gd-trash"></i></a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($programs as $p)
        <form id="tp-form-{{ $p->id }}" method="POST" action="{{ route('training.programs.update', $p) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="tp-del-{{ $p->id }}" method="POST" action="{{ route('training.programs.destroy', $p) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
