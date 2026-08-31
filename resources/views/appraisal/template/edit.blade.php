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

    {{-- KPI Starter --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">KPI / Objective Starter</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-objective-btn">
                    + Tambah KPI
                </button>
            </div>
            <p class="text-muted small mb-2">
                Daftar KPI ini otomatis disalin ke tiap penilaian baru yang dibuat dari template ini (bobot%
                cuma starter — tetap bisa diedit per penilaian). Boleh dikosongkan kalau template ini cuma
                dipakai sebagai starting point kosong.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="objectives-table">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>KPI / Objective</th>
                            <th style="width:150px">Kategori</th>
                            <th style="width:90px">Bobot %</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="objectives-body">
                    @php $objectives = old('objectives', $template->objectives->toArray()); @endphp
                    @foreach($objectives as $i => $obj)
                        <tr class="objective-row">
                            <td class="align-middle text-center text-muted row-num">{{ $i + 1 }}</td>
                            <input type="hidden" name="objectives[{{ $i }}][id]" value="{{ $obj['id'] ?? '' }}">
                            <td>
                                <input type="text" name="objectives[{{ $i }}][title]"
                                       class="form-control form-control-sm"
                                       value="{{ $obj['title'] ?? '' }}" placeholder="Nama KPI" required>
                            </td>
                            <td>
                                <input type="text" name="objectives[{{ $i }}][category]"
                                       class="form-control form-control-sm"
                                       value="{{ $obj['category'] ?? '' }}" placeholder="mis. Financial">
                            </td>
                            <td>
                                <input type="number" name="objectives[{{ $i }}][weight_pct]"
                                       class="form-control form-control-sm text-center"
                                       value="{{ $obj['weight_pct'] ?? 0 }}" min="0" max="100">
                            </td>
                            <td class="align-middle text-center">
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-objective-btn" title="Hapus">
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
    // ── Objective rows ──────────────────────────────────────────────
    function objectiveIndex() {
        return $('#objectives-body .objective-row').length;
    }

    function reindexObjectives() {
        $('#objectives-body .objective-row').each(function (i) {
            $(this).find('.row-num').text(i + 1);
            $(this).find('input').each(function () {
                this.name = this.name.replace(/objectives\[\d+\]/, 'objectives[' + i + ']');
            });
        });
    }

    $('#add-objective-btn').on('click', function () {
        var i = objectiveIndex();
        var row = '<tr class="objective-row">' +
            '<td class="align-middle text-center text-muted row-num">' + (i + 1) + '</td>' +
            '<input type="hidden" name="objectives[' + i + '][id]" value="">' +
            '<td><input type="text" name="objectives[' + i + '][title]" class="form-control form-control-sm" placeholder="Nama KPI" required></td>' +
            '<td><input type="text" name="objectives[' + i + '][category]" class="form-control form-control-sm" placeholder="mis. Financial"></td>' +
            '<td><input type="number" name="objectives[' + i + '][weight_pct]" class="form-control form-control-sm text-center" value="0" min="0" max="100"></td>' +
            '<td class="align-middle text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-objective-btn"><i class="gd-trash"></i></button></td>' +
            '</tr>';
        $('#objectives-body').append(row);
    });

    $(document).on('click', '.remove-objective-btn', function () {
        $(this).closest('tr').remove();
        reindexObjectives();
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
