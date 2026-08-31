@extends('layouts.grain')
@section('title', 'Template Checklist Onboarding')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('recruitment.onboarding.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Onboarding</a>
</div>

<div class="h3 mb-4">Template Checklist Onboarding</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Tambah Item</div>
  <div class="card-body">
    <form method="POST" action="{{ route('recruitment.onboarding.templates.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-5"><label class="small">Nama Item <span class="text-danger">*</span></label>
          <input type="text" name="label" class="form-control form-control-sm" required></div>
        <div class="form-group col-md-2"><label class="small">Kategori</label>
          <select name="category" class="form-control form-control-sm" required>
            @foreach(\App\Models\OnboardingChecklistItem::$categoryLabels as $key => $label)
              <option value="{{ $key }}" @selected($key === 'induction')>{{ $label }}</option>
            @endforeach
          </select></div>
        <div class="form-group col-md-3"><label class="small">Perusahaan</label>
          <select name="company_id" class="form-control form-control-sm">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select></div>
        <div class="form-group col-md-2"><label class="small">Urutan</label>
          <input type="number" name="sort_order" class="form-control form-control-sm" value="0"></div>
      </div>
      <div class="form-group"><label class="small">Deskripsi / instruksi (materi induction)</label>
        <textarea name="description" rows="2" class="form-control form-control-sm"></textarea></div>
      <div class="form-row">
        <div class="form-group col-md-5"><label class="small">Materi — unggah file (pdf/ppt/doc/mp4, maks 50MB)</label>
          <input type="file" name="material" class="form-control form-control-sm"></div>
        <div class="form-group col-md-5"><label class="small">atau Link materi</label>
          <input type="url" name="material_url" class="form-control form-control-sm" placeholder="https://..."></div>
        <div class="form-group col-md-2 d-flex align-items-end"><button class="btn btn-primary btn-sm btn-block">Tambah</button></div>
      </div>
      <div class="custom-control custom-checkbox custom-control-inline">
        <input type="hidden" name="is_required" value="0">
        <input type="checkbox" class="custom-control-input" id="add-req" name="is_required" value="1" checked>
        <label class="custom-control-label small" for="add-req">Wajib</label>
      </div>
      <div class="custom-control custom-checkbox custom-control-inline">
        <input type="checkbox" class="custom-control-input" id="add-ack" name="requires_acknowledgement" value="1">
        <label class="custom-control-label small" for="add-ack">Perlu dibaca &amp; dikonfirmasi karyawan (induction)</label>
      </div>
    </form>
  </div>
</div>

@foreach(\App\Models\OnboardingChecklistItem::$categoryLabels as $catKey => $catLabel)
  @php $catItems = $items->where('category', $catKey); @endphp
  @continue($catItems->isEmpty())
  <div class="card mb-3">
    <div class="card-header font-weight-bold">{{ $catLabel }} <span class="text-muted small">({{ $catItems->count() }})</span></div>
    <div class="card-body p-0">
      @foreach($catItems as $item)
        <details class="border-bottom px-3 py-2">
          <summary style="cursor:pointer">
            <span class="font-weight-bold">{{ $item->sort_order }}. {{ $item->label }}</span>
            <span class="text-muted small">— {{ $item->company?->short_name ?? 'Semua PT' }}</span>
            @if($item->is_required)<span class="badge badge-light border ml-1">wajib</span>@endif
            @if($item->requires_acknowledgement)<span class="badge badge-info ml-1">konfirmasi karyawan</span>@endif
            @if($item->hasMaterial())<span class="badge badge-secondary ml-1"><i class="gd-file"></i> materi</span>@endif
          </summary>
          <form method="POST" action="{{ route('recruitment.onboarding.templates.update', $item) }}" enctype="multipart/form-data" class="mt-2">
            @csrf @method('PUT')
            <div class="form-row">
              <div class="form-group col-md-6"><label class="small">Nama Item</label>
                <input type="text" name="label" value="{{ $item->label }}" class="form-control form-control-sm"></div>
              <div class="form-group col-md-2"><label class="small">Kategori</label>
                <select name="category" class="form-control form-control-sm">
                  @foreach(\App\Models\OnboardingChecklistItem::$categoryLabels as $key => $label)
                    <option value="{{ $key }}" @selected($item->category === $key)>{{ $label }}</option>
                  @endforeach
                </select></div>
              <div class="form-group col-md-2"><label class="small">Perusahaan</label>
                <select name="company_id" class="form-control form-control-sm">
                  <option value="">Semua PT</option>
                  @foreach($companies as $c)<option value="{{ $c->id }}" @selected($item->company_id == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
                </select></div>
              <div class="form-group col-md-2"><label class="small">Urutan</label>
                <input type="number" name="sort_order" value="{{ $item->sort_order }}" class="form-control form-control-sm"></div>
            </div>
            <div class="form-group"><label class="small">Deskripsi / instruksi</label>
              <textarea name="description" rows="2" class="form-control form-control-sm">{{ $item->description }}</textarea></div>
            <div class="form-row">
              <div class="form-group col-md-5"><label class="small">Ganti materi (file)</label>
                <input type="file" name="material" class="form-control form-control-sm">
                @if($item->material_path)
                  <small class="form-text"><a href="{{ route('onboarding.material', $item) }}">{{ $item->material_original_name ?: 'file materi' }}</a> (terpasang)</small>
                @endif
              </div>
              <div class="form-group col-md-5"><label class="small">Link materi</label>
                <input type="url" name="material_url" value="{{ $item->material_url }}" class="form-control form-control-sm"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="custom-control custom-checkbox custom-control-inline">
                  <input type="hidden" name="is_required" value="0">
                  <input type="checkbox" class="custom-control-input" id="req-{{ $item->id }}" name="is_required" value="1" @checked($item->is_required)>
                  <label class="custom-control-label small" for="req-{{ $item->id }}">Wajib</label>
                </div>
                <div class="custom-control custom-checkbox custom-control-inline">
                  <input type="checkbox" class="custom-control-input" id="ack-{{ $item->id }}" name="requires_acknowledgement" value="1" @checked($item->requires_acknowledgement)>
                  <label class="custom-control-label small" for="ack-{{ $item->id }}">Konfirmasi karyawan</label>
                </div>
              </div>
              <div>
                <button type="submit" class="btn btn-sm btn-warning">Simpan</button>
                <a href="#" class="btn btn-sm btn-outline-danger" data-confirm="Hapus item {{ $item->label }}?" data-confirm-title="Hapus Item" data-form="ob-del-{{ $item->id }}">Hapus</a>
              </div>
            </div>
          </form>
          <form id="ob-del-{{ $item->id }}" method="POST" action="{{ route('recruitment.onboarding.templates.destroy', $item) }}" class="d-none">@csrf @method('DELETE')</form>
        </details>
      @endforeach
    </div>
  </div>
@endforeach

@if($items->isEmpty())
  <p class="text-muted small">Belum ada item checklist. Jalankan <code>php artisan db:seed --class=OnboardingChecklistItemSeeder</code> untuk item default.</p>
@endif
@endsection
