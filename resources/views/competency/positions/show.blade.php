@extends('layouts.grain')
@section('title', 'Kompetensi Jabatan — ' . $position->name)

@section('content')
@include('components.notification')

@php $L = \App\Models\Competency\Competency::$levelLabels; @endphp

<div class="mb-3">
  <a href="{{ route('competency.positions.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke daftar jabatan</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="h3 mb-1">{{ $position->name }}</div>
    <div class="text-muted small">
      {{ $position->company?->name ?? 'Semua PT' }}
      @if($position->department) &nbsp;·&nbsp; {{ $position->department->name }} @endif
      @if($position->level) &nbsp;·&nbsp; Level {{ $position->level->name }} @endif
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Kompetensi yang Diwajibkan</div>
  <div class="card-body">

    <form method="POST" action="{{ route('competency.positions.requirements.store', $position) }}" class="form-row align-items-end mb-4">
      @csrf
      <div class="form-group col-md-5 mb-2">
        <label class="small font-weight-bold mb-1">Kompetensi</label>
        <select name="competency_id" class="form-control form-control-sm" required>
          <option value="">-- Pilih kompetensi --</option>
          @foreach($competencies as $c)
            <option value="{{ $c->id }}">{{ $c->category ? '['.$c->category.'] ' : '' }}{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small font-weight-bold mb-1">Level Minimal</label>
        <select name="required_level" class="form-control form-control-sm" required>
          @foreach($L as $n => $lbl)<option value="{{ $n }}">{{ $n }} — {{ $lbl }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small font-weight-bold mb-1">Catatan</label>
        <input type="text" name="notes" class="form-control form-control-sm" maxlength="255">
      </div>
      <div class="form-group col-md-1 mb-2">
        <button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus"></i></button>
      </div>
    </form>

    @if($position->competencyRequirements->isEmpty())
      <p class="text-muted small mb-0">Belum ada kompetensi yang diwajibkan untuk jabatan ini.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="thead-light"><tr><th>Kompetensi</th><th style="width:120px">Kategori</th><th style="width:170px">Level Minimal</th><th>Catatan</th><th style="width:90px"></th></tr></thead>
          <tbody>
          @foreach($position->competencyRequirements->sortBy('competency.name') as $req)
            @php $f = 'req-form-' . $req->id; @endphp
            <tr>
              <td class="align-middle font-weight-bold">{{ $req->competency?->name ?? '(kompetensi terhapus)' }}</td>
              <td class="align-middle">{{ $req->competency?->category ?? '-' }}</td>
              <td>
                <select name="required_level" form="{{ $f }}" class="form-control form-control-sm">
                  @foreach($L as $n => $lbl)<option value="{{ $n }}" @selected($req->required_level == $n)>{{ $n }} — {{ $lbl }}</option>@endforeach
                </select>
              </td>
              <td><input type="text" name="notes" form="{{ $f }}" value="{{ $req->notes }}" class="form-control form-control-sm"></td>
              <td class="text-center align-middle text-nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus kompetensi ini dari profil jabatan?" data-confirm-title="Hapus" data-form="req-del-{{ $req->id }}"><i class="gd-trash"></i></a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @foreach($position->competencyRequirements as $req)
        <form id="req-form-{{ $req->id }}" method="POST" action="{{ route('competency.positions.requirements.update', [$position, $req->id]) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="req-del-{{ $req->id }}" method="POST" action="{{ route('competency.positions.requirements.destroy', [$position, $req->id]) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
