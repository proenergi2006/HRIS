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
                &nbsp;·&nbsp; {{ $appraisal->employee->position }}
                @if($appraisal->employee->department)
                    &nbsp;·&nbsp; {{ $appraisal->employee->department }}
                @endif
            </div>
            <div class="text-muted small">
                Periode: <strong>{{ $appraisal->period->name }}</strong>
                &nbsp;·&nbsp; Template: {{ $appraisal->template->name }}
                &nbsp;·&nbsp; <span class="badge badge-secondary">Draft</span>
            </div>
        </div>

        <form method="POST" action="{{ route('appraisal.appraisals.update', $appraisal) }}" id="form-appraisal">
            @csrf @method('PUT')

            @if($appraisal->template->isWeightedScale())
                {{-- ════════════════════════════════════════════════════
                     FORM WEIGHTED SCALE (Staff / Senior Staff)
                     Skala 1–5 × 4 penilai
                     ════════════════════════════════════════════════════ --}}

                @if($isKaryawan)
                <div class="alert alert-primary py-2 mb-3" style="font-size:0.88rem;">
                    <strong>Penilaian Mandiri (Self-Assessment):</strong>
                    Silakan isi kolom <strong>Diri Sendiri</strong> dengan jujur.
                    Kolom lainnya (Atasan I, Atasan II, Head Office) akan diisi oleh atasan Anda.
                </div>
                @endif

                @php
                    $evalLabels = ['self' => 'Diri Sendiri', 'atasan1' => 'Atasan I', 'atasan2' => 'Atasan II', 'ho' => 'Head Office'];
                    $ratingLabels = [1 => 'Kurang Sekali', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Baik Sekali'];
                @endphp

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm mb-0" id="table-scale">
                        <thead class="thead-light">
                            <tr>
                                <th rowspan="2" style="width:32px;vertical-align:middle">#</th>
                                <th rowspan="2" style="vertical-align:middle">Faktor Penilaian</th>
                                <th rowspan="2" class="text-center" style="width:60px;vertical-align:middle">Bobot</th>
                                @foreach($evalLabels as $key => $label)
                                    <th class="text-center" style="width:110px">{{ $label }}<br><small class="text-muted">(1–5)</small></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($appraisal->template->aspects as $aspect)
                            <tr>
                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle">{{ $aspect->name }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-light">{{ $aspect->weight_pct }}%</span>
                                </td>
                                @foreach(array_keys($evalLabels) as $evalType)
                                    @php $item = $itemsByEvaluator->get($evalType)?->get($aspect->id); @endphp
                                    <td class="text-center align-middle p-1">
                                        @if($isKaryawan && $evalType !== 'self')
                                            {{-- Karyawan: kolom selain Diri Sendiri hanya tampil --}}
                                            <span class="badge badge-{{ $item?->rating ? 'primary' : 'light' }} p-1" style="font-size:0.9rem;">
                                                {{ $item?->rating ?? '–' }}
                                            </span>
                                        @else
                                            <select name="ratings[{{ $evalType }}][{{ $aspect->id }}]"
                                                    class="form-control form-control-sm scale-select text-center"
                                                    data-type="{{ $evalType }}"
                                                    data-aspect="{{ $aspect->id }}"
                                                    data-weight="{{ $aspect->weight_pct }}"
                                                    style="width:70px;margin:0 auto;">
                                                <option value="">–</option>
                                                @foreach($ratingLabels as $val => $lbl)
                                                    <option value="{{ $val }}"
                                                        {{ old("ratings.{$evalType}.{$aspect->id}", $item?->rating) == $val ? 'selected' : '' }}>
                                                        {{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="thead-light">
                            <tr>
                                <td colspan="3" class="text-right font-weight-bold py-2">Skor (maks 500)</td>
                                @foreach(array_keys($evalLabels) as $evalType)
                                    <td class="text-center font-weight-bold" id="score-{{ $evalType }}">–</td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-info py-2 mb-4" style="font-size:0.83rem;">
                    <strong>Skala:</strong>
                    1 = Kurang Sekali &nbsp;|&nbsp; 2 = Kurang &nbsp;|&nbsp; 3 = Cukup &nbsp;|&nbsp; 4 = Baik &nbsp;|&nbsp; 5 = Baik Sekali<br>
                    <strong>Skor per faktor</strong> = Nilai × Bobot.
                    <strong>Total maks</strong> = 500.
                </div>

                @if(!$isKaryawan)
                {{-- Kolom Kualitatif — hanya diisi evaluator, bukan karyawan --}}
                <div class="card card-frame mb-3">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-3">Penilaian Kualitatif</h6>
                        <div class="form-group">
                            <label for="strength_points">Strength Point <small class="text-muted">(Kelebihan / Potensi)</small></label>
                            <textarea id="strength_points" name="strength_points" rows="3"
                                      class="form-control"
                                      placeholder="Uraikan kelebihan dan potensi karyawan...">{{ old('strength_points', $appraisal->strength_points) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="development_need">Development Need Area <small class="text-muted">(Area yang Perlu Dikembangkan)</small></label>
                            <textarea id="development_need" name="development_need" rows="3"
                                      class="form-control"
                                      placeholder="Area yang memerlukan pengembangan lebih lanjut...">{{ old('development_need', $appraisal->development_need) }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label for="individual_development_plan">Individual Development Plan (IDP)</label>
                            <textarea id="individual_development_plan" name="individual_development_plan" rows="3"
                                      class="form-control"
                                      placeholder="Rencana pengembangan individu yang direkomendasikan...">{{ old('individual_development_plan', $appraisal->individual_development_plan) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="form-group">
                    <label for="notes">Catatan Evaluator</label>
                    <textarea id="notes" name="notes" rows="2"
                              class="form-control"
                              placeholder="Catatan tambahan...">{{ old('notes', $appraisal->notes) }}</textarea>
                </div>
                @endif

            @else
                {{-- ════════════════════════════════════════════════════
                     FORM FIXED POINTS (SPV / Manager / Admin)
                     BS / B / C / K
                     ════════════════════════════════════════════════════ --}}

                <div class="table-responsive mb-4">
                    <table class="table table-bordered mb-0" id="table-aspects">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Aspek Penilaian</th>
                                <th class="text-center" style="width:90px">Baik Sekali<br><small class="text-muted">(BS)</small></th>
                                <th class="text-center" style="width:80px">Baik<br><small class="text-muted">(B)</small></th>
                                <th class="text-center" style="width:80px">Cukup<br><small class="text-muted">(C)</small></th>
                                <th class="text-center" style="width:80px">Kurang<br><small class="text-muted">(K)</small></th>
                                <th class="text-center" style="width:80px">Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($appraisal->template->aspects as $aspect)
                            @php
                                $item          = $itemsByAspect->get($aspect->id);
                                $currentRating = $item?->rating;
                                $weights       = $aspect->weights->keyBy('rating');
                            @endphp
                            <tr>
                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle">{{ $aspect->name }}</td>
                                @foreach(['BS','B','C','K'] as $rating)
                                    <td class="text-center align-middle">
                                        <input type="radio"
                                               name="ratings[{{ $aspect->id }}]"
                                               value="{{ $rating }}"
                                               class="rating-radio"
                                               data-aspect="{{ $aspect->id }}"
                                               {{ $currentRating === $rating ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                                <td class="text-center align-middle score-cell" id="score-{{ $aspect->id }}">
                                    {{ $item?->score ?? 0 }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="thead-light">
                            <tr>
                                <td colspan="6" class="text-right font-weight-bold py-2">Total Skor</td>
                                <td class="text-center font-weight-bold py-2" id="total-score">
                                    {{ $appraisal->total_score ?? 0 }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-right font-weight-bold py-2">Grade</td>
                                <td class="text-center font-weight-bold py-2" id="grade-display">
                                    {{ $appraisal->grade ?? '-' }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Data Absensi --}}
                <div class="card card-frame mb-3">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-3">Data Absensi</h6>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-4">
                                <label>Rata-rata Keterlambatan <small class="text-muted">(hari/bulan)</small></label>
                                <input type="number" step="0.01" min="0" name="avg_late_per_month"
                                       class="form-control"
                                       value="{{ old('avg_late_per_month', $appraisal->avg_late_per_month) }}">
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label>Rata-rata Tidak Hadir <small class="text-muted">(hari/bulan)</small></label>
                                <input type="number" step="0.01" min="0" name="avg_leave_per_month"
                                       class="form-control"
                                       value="{{ old('avg_leave_per_month', $appraisal->avg_leave_per_month) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Usulan --}}
                <div class="card card-frame mb-3">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-3">Usulan</h6>
                        <div class="form-row align-items-center">
                            <div class="form-group col-12 col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="hidden" name="warning_letter" value="0">
                                    <input type="checkbox" class="custom-control-input" id="warning_letter"
                                           name="warning_letter" value="1"
                                           {{ old('warning_letter', $appraisal->warning_letter) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="warning_letter">Surat Teguran</label>
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label>Surat Peringatan</label>
                                <select name="sp_level" class="form-control">
                                    <option value="none"  {{ old('sp_level', $appraisal->sp_level) === 'none' ? 'selected' : '' }}>Tidak Ada</option>
                                    <option value="sp1"   {{ old('sp_level', $appraisal->sp_level) === 'sp1'  ? 'selected' : '' }}>SP 1</option>
                                    <option value="sp2"   {{ old('sp_level', $appraisal->sp_level) === 'sp2'  ? 'selected' : '' }}>SP 2</option>
                                    <option value="sp3"   {{ old('sp_level', $appraisal->sp_level) === 'sp3'  ? 'selected' : '' }}>SP 3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="form-group">
                    <label for="notes">Catatan / Rekomendasi</label>
                    <textarea id="notes" name="notes" rows="3" class="form-control"
                              placeholder="Catatan tambahan dari penilai...">{{ old('notes', $appraisal->notes) }}</textarea>
                </div>

            @endif

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
@if($appraisal->template->isWeightedScale())
<script>
var gradeBands = {!! json_encode(
    $appraisal->template->gradeBands->map(fn($b) => ['min' => $b->min_score, 'label' => $b->grade_label])
) !!};
var isKaryawan = {{ $isKaryawan ? 'true' : 'false' }};

function recalcScale() {
    var types = isKaryawan ? ['self'] : ['self', 'atasan1', 'atasan2', 'ho'];
    types.forEach(function(type) {
        var total = 0;
        var allFilled = true;
        $('[data-type="' + type + '"]').each(function() {
            var val = parseInt($(this).val());
            var weight = parseInt($(this).data('weight'));
            if (val) {
                total += val * weight;
            } else {
                allFilled = false;
            }
        });
        $('#score-' + type).text(allFilled ? total.toFixed(0) : '–');
    });
}
$(document).ready(function() {
    recalcScale();
    $(document).on('change', '.scale-select', recalcScale);
});
</script>
@else
<script>
var weights = {!! json_encode(
    $appraisal->template->aspects->mapWithKeys(function($aspect) {
        return [$aspect->id => $aspect->weights->pluck('score', 'rating')];
    })
) !!};
var gradeBands = {!! json_encode(
    $appraisal->template->gradeBands->map(fn($b) => ['min' => $b->min_score, 'label' => $b->grade_label])
) !!};

function recalculate() {
    var total = 0;
    $('.rating-radio').each(function() {
        var aspectId = $(this).data('aspect');
        var anyChecked = $('input[name="ratings[' + aspectId + ']"]:checked').length > 0;
        if (!anyChecked) { $('#score-' + aspectId).text(0); }
    });
    $('.rating-radio:checked').each(function() {
        var aspectId = $(this).data('aspect');
        var rating   = $(this).val();
        var score    = (weights[aspectId] && weights[aspectId][rating]) ? parseInt(weights[aspectId][rating]) : 0;
        $('#score-' + aspectId).text(score);
        total += score;
    });
    $('#total-score').text(total);
    var grade = '-';
    for (var i = 0; i < gradeBands.length; i++) {
        if (total >= gradeBands[i].min) { grade = gradeBands[i].label; break; }
    }
    $('#grade-display').text(grade);
}
$(document).ready(function() {
    recalculate();
    $(document).on('change', '.rating-radio', recalculate);
});
</script>
@endif
@endsection
