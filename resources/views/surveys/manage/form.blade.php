@extends('layouts.grain')
@section('title', $survey->exists ? 'Edit Survey' : 'Survey Baru')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('surveys.manage.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">{{ $survey->exists ? 'Edit Survey' : 'Survey Baru' }}</div>

@if($survey->exists && $survey->status !== 'draft')
  <div class="alert alert-warning py-2 px-3 small">Survey sudah {{ $survey->status === 'open' ? 'dibuka' : 'ditutup' }} —
  mengubah pertanyaan tetap bisa, tapi hati-hati kalau sudah ada responden.</div>
@endif

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $survey->exists ? route('surveys.manage.update', $survey) : route('surveys.manage.store') }}">
      @csrf
      @if($survey->exists) @method('PUT') @endif

      <div class="form-row">
        <div class="form-group col-md-5">
          <label>Judul <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" value="{{ old('title', $survey->title) }}" required>
        </div>
        <div class="form-group col-md-3">
          <label>Tipe</label>
          <select name="type" class="form-control">
            @foreach(\App\Models\Survey\Survey::$typeLabels as $k => $lbl)
              <option value="{{ $k }}" @selected(old('type', $survey->type ?? 'standard') === $k)>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-2">
          <label>Perusahaan</label>
          <select name="company_id" class="form-control">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id', $survey->company_id) == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2">
          <label class="d-block">&nbsp;</label>
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="anon" name="is_anonymous" value="1" @checked(old('is_anonymous', $survey->is_anonymous ?? true))>
            <label class="custom-control-label" for="anon">Anonim</label>
          </div>
        </div>
      </div>
      <div class="alert alert-secondary small py-2 px-3 mb-3" id="type-hint"></div>
      <script>
        (function () {
          var hints = {
            standard: 'Survey satu-kali biasa — bebas jenis pertanyaan.',
            pulse: 'Survey ringkas berkala (mis. bulanan/kuartalan) — sebaiknya 3-5 pertanyaan singkat. Setelah dibuka & ditutup, gunakan tombol "Duplikat" untuk membuat putaran berikutnya.',
            enps: 'eNPS — tambahkan TEPAT 1 pertanyaan bertipe "Skala 0-10" berbunyi seperti "Seberapa besar kemungkinan Anda merekomendasikan tempat kerja ini ke teman/kolega?". Skor eNPS dihitung otomatis (%Promoter 9-10 dikurangi %Detraktor 0-6) dan bisa dilihat trennya di menu Tren eNPS.',
          };
          var sel = document.querySelector('select[name="type"]');
          var hint = document.getElementById('type-hint');
          function update() { hint.textContent = hints[sel.value] || ''; }
          sel.addEventListener('change', update);
          update();
        })();
      </script>
      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="description" rows="2" class="form-control">{{ old('description', $survey->description) }}</textarea>
      </div>
      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Mulai</label>
          <input type="date" name="opens_at" class="form-control" value="{{ old('opens_at', $survey->opens_at?->format('Y-m-d')) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Selesai</label>
          <input type="date" name="closes_at" class="form-control" value="{{ old('closes_at', $survey->closes_at?->format('Y-m-d')) }}">
        </div>
      </div>

      <hr>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Pertanyaan</strong>
        <button type="button" id="add-q" class="btn btn-sm btn-outline-primary"><i class="gd-plus mr-1"></i> Tambah Pertanyaan</button>
      </div>
      <div id="q-wrap"></div>

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('surveys.manage.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<template id="q-template">
  <div class="border rounded p-3 mb-2 q-row">
    <div class="form-row">
      <div class="form-group col-md-6 mb-2">
        <label class="small mb-1">Pertanyaan</label>
        <input type="text" class="form-control form-control-sm q-text" placeholder="Tulis pertanyaan...">
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small mb-1">Tipe</label>
        <select class="form-control form-control-sm q-type">
          <option value="scale">Skala 0-10</option>
          <option value="text">Teks Bebas</option>
          <option value="single">Pilihan Tunggal</option>
          <option value="multi">Pilihan Ganda</option>
        </select>
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small mb-1">&nbsp;</label>
        <div class="custom-control custom-checkbox mt-1">
          <input type="checkbox" class="custom-control-input q-required" checked>
          <label class="custom-control-label small">Wajib</label>
        </div>
      </div>
    </div>
    <div class="form-group mb-2 q-options-wrap" style="display:none">
      <label class="small mb-1">Pilihan (pisahkan koma)</label>
      <input type="text" class="form-control form-control-sm q-options" placeholder="Sangat Puas, Puas, Kurang Puas">
    </div>
    <button type="button" class="btn btn-xs btn-outline-danger q-remove">Hapus Pertanyaan</button>
  </div>
</template>

<script>
(function () {
  var wrap = document.getElementById('q-wrap');
  var tpl  = document.getElementById('q-template');
  var idx  = 0;

  function addRow(data) {
    data = data || {};
    var node = tpl.content.cloneNode(true);
    var row = node.querySelector('.q-row');
    var i = idx++;

    var textInput = row.querySelector('.q-text');
    textInput.name = 'questions[' + i + '][text]';
    textInput.value = data.text || '';

    var typeSel = row.querySelector('.q-type');
    typeSel.name = 'questions[' + i + '][type]';
    if (data.type) typeSel.value = data.type;

    var reqBox = row.querySelector('.q-required');
    reqBox.name = 'questions[' + i + '][is_required]';
    reqBox.value = '1';
    reqBox.checked = data.is_required !== false;

    var optWrap = row.querySelector('.q-options-wrap');
    var optInput = row.querySelector('.q-options');
    optInput.name = 'questions[' + i + '][options]';
    optInput.value = data.options || '';
    optWrap.style.display = (typeSel.value === 'single' || typeSel.value === 'multi') ? '' : 'none';

    typeSel.addEventListener('change', function () {
      optWrap.style.display = (this.value === 'single' || this.value === 'multi') ? '' : 'none';
    });

    row.querySelector('.q-remove').addEventListener('click', function () {
      row.remove();
    });

    wrap.appendChild(row);
  }

  document.getElementById('add-q').addEventListener('click', function () { addRow(); });

  @php
    $existingQuestions = $questions->map(fn ($q) => [
        'text' => $q->text, 'type' => $q->type, 'is_required' => $q->is_required,
        'options' => $q->options ? implode(', ', $q->options) : '',
    ]);
  @endphp
  var existing = @json($existingQuestions);
  if (existing.length) {
    existing.forEach(addRow);
  } else {
    addRow();
  }
})();
</script>
@endsection
