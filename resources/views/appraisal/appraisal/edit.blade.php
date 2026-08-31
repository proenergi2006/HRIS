@extends('layouts.grain')
@section('title', 'Isi Penilaian Kinerja')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appraisal.appraisals.index') }}">Data Penilaian</a></li>
                <li class="breadcrumb-item active">Isi Penilaian</li>
            </ol>
        </nav>

        <div class="mb-3 mb-md-4">
            <div class="h3 mb-1">Penilaian Kinerja Karyawan</div>
            <div class="text-muted">
                <strong>{{ $appraisal->employee->name }}</strong>
                &nbsp;·&nbsp; {{ $appraisal->employee->position?->name }}
                @if($appraisal->employee->department)
                    &nbsp;·&nbsp; {{ $appraisal->employee->department->name }}
                @endif
            </div>
            <div class="text-muted small">
                Periode: <strong>{{ $appraisal->period->name }}</strong>
                @if($appraisal->template) &nbsp;·&nbsp; Template: {{ $appraisal->template->name }} @endif
                &nbsp;·&nbsp; <span class="badge badge-secondary">Draft</span>
            </div>
        </div>

        <form method="POST" action="{{ route('appraisal.appraisals.update', $appraisal) }}" id="form-appraisal">
            @csrf @method('PUT')

            <p class="text-muted small">
                Tiap KPI/objective punya bobot (%) — total bobot semua baris harus <strong>100%</strong>
                sebelum penilaian bisa disubmit. Skor per baris = bobot% × capaian%.
            </p>

            <div class="table-responsive mb-2">
                <table class="table table-bordered table-sm mb-0" id="table-objectives">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:32px">#</th>
                            <th style="min-width:160px">KPI / Objective</th>
                            <th style="width:130px">Kategori</th>
                            <th style="width:70px">Bobot %</th>
                            <th style="min-width:150px">Target</th>
                            <th style="min-width:150px">Realisasi</th>
                            <th style="width:90px">Capaian %</th>
                            <th style="width:80px">Skor</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="objectives-body">
                    @forelse($appraisal->objectives as $i => $obj)
                        <tr class="objective-row">
                            <td class="align-middle text-center text-muted row-num">{{ $i + 1 }}</td>
                            <input type="hidden" name="objectives[{{ $i }}][id]" value="{{ $obj->id }}">
                            <td><input type="text" name="objectives[{{ $i }}][title]" class="form-control form-control-sm" value="{{ $obj->title }}" required></td>
                            <td><input type="text" name="objectives[{{ $i }}][category]" class="form-control form-control-sm" value="{{ $obj->category }}" placeholder="mis. Financial"></td>
                            <td><input type="number" name="objectives[{{ $i }}][weight_pct]" class="form-control form-control-sm text-center weight-input" value="{{ $obj->weight_pct }}" min="0" max="100"></td>
                            <td><input type="text" name="objectives[{{ $i }}][target]" class="form-control form-control-sm" value="{{ $obj->target }}"></td>
                            <td><input type="text" name="objectives[{{ $i }}][actual]" class="form-control form-control-sm" value="{{ $obj->actual }}"></td>
                            <td><input type="number" step="0.01" name="objectives[{{ $i }}][achievement_pct]" class="form-control form-control-sm text-center achievement-input" value="{{ $obj->achievement_pct }}" min="0"></td>
                            <td class="text-center align-middle score-cell">{{ $obj->score ?? '-' }}</td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-objective-btn"><i class="gd-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <td colspan="3" class="text-right font-weight-bold py-2">Total Bobot</td>
                            <td class="text-center font-weight-bold py-2" id="total-weight">
                                {{ $appraisal->objectives->sum('weight_pct') }}
                            </td>
                            <td colspan="3"></td>
                            <td class="text-center font-weight-bold py-2" id="total-score">
                                {{ $appraisal->total_score ?? 0 }}
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="8" class="text-right font-weight-bold py-2">Grade <span id="grade-display" class="ml-2 badge badge-info">{{ $appraisal->grade ?? '-' }}</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-4" id="add-objective-btn">
                <i class="gd-plus mr-1"></i> Tambah KPI
            </button>

            <div class="card card-frame mb-3">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Catatan Kualitatif</h6>
                    <div class="form-group">
                        <label for="strengths">Kekuatan / Strength Point</label>
                        <textarea id="strengths" name="strengths" rows="2" class="form-control"
                                  placeholder="Uraikan kelebihan dan potensi karyawan...">{{ old('strengths', $appraisal->strengths) }}</textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label for="development_notes">Area Pengembangan</label>
                        <textarea id="development_notes" name="development_notes" rows="2" class="form-control"
                                  placeholder="Area yang memerlukan pengembangan lebih lanjut...">{{ old('development_notes', $appraisal->development_notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Catatan Evaluator</label>
                <textarea id="notes" name="notes" rows="2" class="form-control"
                          placeholder="Catatan tambahan...">{{ old('notes', $appraisal->notes) }}</textarea>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('appraisal.appraisals.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">
                    <i class="gd-check mr-1"></i> Simpan Draft
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function ($) {
    var gradeBands = {!! json_encode(
        $appraisal->template?->gradeBands->map(fn($b) => ['min' => $b->min_score, 'label' => $b->grade_label]) ?? []
    ) !!};

    function rowIndex() { return $('#objectives-body .objective-row').length; }

    function reindex() {
        $('#objectives-body .objective-row').each(function (i) {
            $(this).find('.row-num').text(i + 1);
            $(this).find('input').each(function () {
                this.name = this.name.replace(/objectives\[\d+\]/, 'objectives[' + i + ']');
            });
        });
    }

    $('#add-objective-btn').on('click', function () {
        var i = rowIndex();
        var row = '<tr class="objective-row">' +
            '<td class="align-middle text-center text-muted row-num">' + (i + 1) + '</td>' +
            '<input type="hidden" name="objectives[' + i + '][id]" value="">' +
            '<td><input type="text" name="objectives[' + i + '][title]" class="form-control form-control-sm" required></td>' +
            '<td><input type="text" name="objectives[' + i + '][category]" class="form-control form-control-sm" placeholder="mis. Financial"></td>' +
            '<td><input type="number" name="objectives[' + i + '][weight_pct]" class="form-control form-control-sm text-center weight-input" value="0" min="0" max="100"></td>' +
            '<td><input type="text" name="objectives[' + i + '][target]" class="form-control form-control-sm"></td>' +
            '<td><input type="text" name="objectives[' + i + '][actual]" class="form-control form-control-sm"></td>' +
            '<td><input type="number" step="0.01" name="objectives[' + i + '][achievement_pct]" class="form-control form-control-sm text-center achievement-input" min="0"></td>' +
            '<td class="text-center align-middle score-cell">-</td>' +
            '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-objective-btn"><i class="gd-trash"></i></button></td>' +
            '</tr>';
        $('#objectives-body').append(row);
    });

    $(document).on('click', '.remove-objective-btn', function () {
        $(this).closest('tr').remove();
        reindex();
        recalc();
    });

    function recalc() {
        var totalWeight = 0, totalScore = 0;
        $('.objective-row').each(function () {
            var weight = parseFloat($(this).find('.weight-input').val()) || 0;
            var achievement = $(this).find('.achievement-input').val();
            totalWeight += weight;
            if (achievement !== '') {
                var score = Math.round((weight * parseFloat(achievement) / 100) * 100) / 100;
                $(this).find('.score-cell').text(score);
                totalScore += score;
            } else {
                $(this).find('.score-cell').text('-');
            }
        });
        $('#total-weight').text(totalWeight).css('color', totalWeight === 100 ? '' : '#dc3545');
        $('#total-score').text(totalScore.toFixed(2));

        var grade = '-';
        for (var i = 0; i < gradeBands.length; i++) {
            if (totalScore >= gradeBands[i].min) { grade = gradeBands[i].label; break; }
        }
        $('#grade-display').text(grade);
    }

    $(document).on('input', '.weight-input, .achievement-input', recalc);
    recalc();
})(jQuery);
</script>
@endsection
