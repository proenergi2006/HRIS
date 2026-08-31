@extends('layouts.grain')
@section('title', 'Template Checklist Clearance Resign')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('hr.offboarding.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Clearance Resign</a>
</div>

<div class="h3 mb-4">Template Checklist Clearance Resign</div>

<div class="card">
  <div class="card-header font-weight-bold">Daftar Item</div>
  <div class="card-body">

    <form method="POST" action="{{ route('hr.offboarding.templates.store') }}" class="mb-4">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-4 mb-2">
          <input type="text" name="label" class="form-control" placeholder="Nama item (mis. Kembalikan laptop)" required>
        </div>
        <div class="form-group col-md-2 mb-2">
          <select name="category" class="form-control" required>
            @foreach(\App\Models\OffboardingChecklistItem::$categoryLabels as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3 mb-2">
          <select name="company_id" class="form-control">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2 mb-2">
          <input type="number" name="sort_order" class="form-control" placeholder="Urutan" value="0">
        </div>
        <div class="form-group col-md-1 mb-2">
          <button type="submit" class="btn btn-primary btn-block">+</button>
        </div>
      </div>
    </form>

    @if($items->isEmpty())
      <p class="text-muted small">Belum ada item checklist.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Item</th><th>Kategori</th><th>PT</th><th class="text-center">Wajib</th><th></th></tr>
          </thead>
          <tbody>
          @foreach($items as $item)
            @php $f = 'ofb-form-' . $item->id; @endphp
            <tr>
              <td style="min-width:220px"><input type="text" name="label" form="{{ $f }}" value="{{ $item->label }}" class="form-control form-control-sm"></td>
              <td style="width:150px">
                <select name="category" form="{{ $f }}" class="form-control form-control-sm">
                  @foreach(\App\Models\OffboardingChecklistItem::$categoryLabels as $key => $label)
                    <option value="{{ $key }}" @selected($item->category === $key)>{{ $label }}</option>
                  @endforeach
                </select>
              </td>
              <td style="width:130px">{{ $item->company?->short_name ?? 'Semua PT' }}</td>
              <td class="text-center" style="width:80px">
                <input type="hidden" name="is_required" form="{{ $f }}" value="0">
                <input type="checkbox" name="is_required" form="{{ $f }}" value="1" {{ $item->is_required ? 'checked' : '' }}>
              </td>
              <td style="width:90px;white-space:nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <button type="submit" form="ofb-del-{{ $item->id }}" class="btn btn-xs btn-outline-danger" onclick="return confirm('Hapus item {{ $item->label }}?')"><i class="gd-trash"></i></button>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($items as $item)
        <form id="ofb-form-{{ $item->id }}" method="POST" action="{{ route('hr.offboarding.templates.update', $item) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="ofb-del-{{ $item->id }}" method="POST" action="{{ route('hr.offboarding.templates.destroy', $item) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
