@extends('layouts.grain')
@section('title', $template->id ? 'Edit Template' : 'Tambah Template')

@section('content')
@include('components.notification')

<form method="POST" action="{{ $template->id ? route('appraisal.templates.update', $template) : route('appraisal.templates.store') }}" id="template-form">
    @csrf
    @if($template->id) @method('PUT') @endif

    {{-- Header --}}
    <div class="card mb-3">
        <div class="card-body">
            <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('appraisal.templates.index') }}">Template Penilaian</a></li>
                    <li class="breadcrumb-item active">{{ $template->id ? 'Edit' : 'Tambah' }}</li>
                </ol>
            </nav>
            <div class="h3 mb-3">{{ $template->id ? 'Edit Template: ' . $template->name : 'Tambah Template Penilaian' }}</div>

            <div class="form-row">
                <div class="form-group col-12 col-md-5">
                    <label for="name">Nama Template <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                           value="{{ old('name', $template->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-4">
                    <label for="level_id">Level Jabatan</label>
                    <select id="level_id" name="level_id" class="form-control">
                        <option value="">-- Semua Level --</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" {{ old('level_id', $template->level_id) == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" id="is_default" name="is_default" value="1" class="form-check-input"
                               {{ old('is_default', $template->is_default) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">Jadikan Default</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Aspek & Bobot --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Aspek Penilaian & Bobot</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-aspect-btn">
                    + Tambah Aspek
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="aspects-table">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Nama Aspek</th>
                            <th class="text-center" style="width:90px">BS</th>
                            <th class="text-center" style="width:90px">B</th>
                            <th class="text-center" style="width:90px">C</th>
                            <th class="text-center" style="width:90px">K</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="aspects-body">
                    @php $aspects = old('aspects', $template->aspects->toArray()); @endphp
                    @foreach($aspects as $i => $aspect)
                        @php
                            $weights = [];
                            if (isset($aspect['weights']) && is_array($aspect['weights'])) {
                                // Dari old() sudah berupa array rating=>score
                                $weights = $aspect['weights'];
                            } elseif (isset($aspect['weights'])) {
                                // Dari model: collection
                                foreach (($aspect['weights'] ?? []) as $w) {
                                    $weights[$w['rating']] = $w['score'];
                                }
                            }
                        @endphp
                        <tr class="aspect-row">
                            <td class="align-middle text-center text-muted row-num">{{ $i + 1 }}</td>
                            <input type="hidden" name="aspects[{{ $i }}][id]" value="{{ $aspect['id'] ?? '' }}">
                            <td>
                                <input type="text" name="aspects[{{ $i }}][name]"
                                       class="form-control form-control-sm"
                                       value="{{ $aspect['name'] ?? '' }}" placeholder="Nama aspek" required>
                            </td>
                            @foreach(['BS','B','C','K'] as $r)
                            <td>
                                <input type="number" name="aspects[{{ $i }}][weights][{{ $r }}]"
                                       class="form-control form-control-sm text-center"
                                       value="{{ $weights[$r] ?? 0 }}" min="0">
                            </td>
                            @endforeach
                            <td class="align-middle text-center">
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-aspect-btn" title="Hapus">
                                    <i class="gd-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Grade Bands --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Grade / Predikat</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-band-btn">
                    + Tambah Grade
                </button>
            </div>
            <p class="text-muted small mb-2">Grade diberikan berdasarkan total skor ≥ nilai minimum. Urutkan dari skor tertinggi ke terendah.</p>

            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="bands-table">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Predikat</th>
                            <th style="width:160px">Skor Minimum (≥)</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="bands-body">
                    @php $bands = old('grade_bands', $template->gradeBands->toArray()); @endphp
                    @foreach($bands as $i => $band)
                        <tr class="band-row">
                            <td class="align-middle text-center text-muted band-num">{{ $i + 1 }}</td>
                            <td>
                                <input type="text" name="grade_bands[{{ $i }}][grade_label]"
                                       class="form-control form-control-sm"
                                       value="{{ $band['grade_label'] ?? '' }}" placeholder="cth: Baik Sekali" required>
                            </td>
                            <td>
                                <input type="number" name="grade_bands[{{ $i }}][min_score]"
                                       class="form-control form-control-sm"
                                       value="{{ $band['min_score'] ?? 0 }}" min="0">
                            </td>
                            <td class="align-middle text-center">
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-band-btn">
                                    <i class="gd-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tombol --}}
    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('appraisal.templates.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary px-4">{{ $template->id ? 'Simpan Perubahan' : 'Buat Template' }}</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
(function ($) {
    // ── Aspect rows ──────────────────────────────────────────────
    function aspectIndex() {
        return $('#aspects-body .aspect-row').length;
    }

    function reindexAspects() {
        $('#aspects-body .aspect-row').each(function (i) {
            $(this).find('.row-num').text(i + 1);
            $(this).find('input').each(function () {
                this.name = this.name.replace(/aspects\[\d+\]/, 'aspects[' + i + ']');
            });
        });
    }

    $('#add-aspect-btn').on('click', function () {
        var i = aspectIndex();
        var row = '<tr class="aspect-row">' +
            '<td class="align-middle text-center text-muted row-num">' + (i + 1) + '</td>' +
            '<input type="hidden" name="aspects[' + i + '][id]" value="">' +
            '<td><input type="text" name="aspects[' + i + '][name]" class="form-control form-control-sm" placeholder="Nama aspek" required></td>' +
            ['BS','B','C','K'].map(function(r){
                return '<td><input type="number" name="aspects[' + i + '][weights][' + r + ']" class="form-control form-control-sm text-center" value="0" min="0"></td>';
            }).join('') +
            '<td class="align-middle text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-aspect-btn"><i class="gd-trash"></i></button></td>' +
            '</tr>';
        $('#aspects-body').append(row);
    });

    $(document).on('click', '.remove-aspect-btn', function () {
        if ($('#aspects-body .aspect-row').length <= 1) {
            alert('Template harus memiliki minimal 1 aspek.');
            return;
        }
        $(this).closest('tr').remove();
        reindexAspects();
    });

    // ── Grade band rows ──────────────────────────────────────────
    function bandIndex() {
        return $('#bands-body .band-row').length;
    }

    function reindexBands() {
        $('#bands-body .band-row').each(function (i) {
            $(this).find('.band-num').text(i + 1);
            $(this).find('input').each(function () {
                this.name = this.name.replace(/grade_bands\[\d+\]/, 'grade_bands[' + i + ']');
            });
        });
    }

    $('#add-band-btn').on('click', function () {
        var i = bandIndex();
        var row = '<tr class="band-row">' +
            '<td class="align-middle text-center text-muted band-num">' + (i + 1) + '</td>' +
            '<td><input type="text" name="grade_bands[' + i + '][grade_label]" class="form-control form-control-sm" placeholder="cth: Baik Sekali" required></td>' +
            '<td><input type="number" name="grade_bands[' + i + '][min_score]" class="form-control form-control-sm" value="0" min="0"></td>' +
            '<td class="align-middle text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-band-btn"><i class="gd-trash"></i></button></td>' +
            '</tr>';
        $('#bands-body').append(row);
    });

    $(document).on('click', '.remove-band-btn', function () {
        $(this).closest('tr').remove();
        reindexBands();
    });
})(jQuery);
</script>
@endsection
