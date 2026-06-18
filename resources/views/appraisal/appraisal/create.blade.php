@extends('layouts.grain')
@section('title', 'Buat Penilaian Kinerja')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appraisal.appraisals.index') }}">Data Penilaian</a></li>
                <li class="breadcrumb-item active">Buat Penilaian</li>
            </ol>
        </nav>

        <div class="mb-3 mb-md-4">
            <div class="h3 mb-0">Buat Penilaian Kinerja Baru</div>
        </div>

        <form method="POST" action="{{ route('appraisal.appraisals.store') }}">
            @csrf

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label>Karyawan yang Dinilai</label>
                    @if(auth()->user()->hasRole('karyawan'))
                        @php $myEmp = $employees->first(); @endphp
                        <input type="hidden" name="employee_id" value="{{ $myEmp->id }}">
                        <input type="text" class="form-control" readonly
                               value="{{ $myEmp->name }}{{ $myEmp->nip ? ' ('.$myEmp->nip.')' : '' }} — {{ $myEmp->position }}">
                        <small class="text-muted">Penilaian mandiri (self-assessment) untuk diri sendiri.</small>
                    @else
                        <select id="employee_id" name="employee_id"
                                class="form-control{{ $errors->has('employee_id') ? ' is-invalid' : '' }}">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}"
                                        data-level="{{ $emp->level_id }}"
                                        {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                    @if($emp->nip) ({{ $emp->nip }}) @endif
                                    — {{ $emp->position }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="appraisal_period_id">Periode Penilaian <span class="text-danger">*</span></label>
                    <select id="appraisal_period_id" name="appraisal_period_id"
                            class="form-control{{ $errors->has('appraisal_period_id') ? ' is-invalid' : '' }}">
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}"
                                    {{ old('appraisal_period_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->name }} ({{ $period->year }})
                            </option>
                        @endforeach
                    </select>
                    @error('appraisal_period_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if($periods->isEmpty())
                        <small class="text-warning">Tidak ada periode yang sedang buka.
                            <a href="{{ route('appraisal.periods.index') }}">Buka periode terlebih dahulu.</a>
                        </small>
                    @endif
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="appraisal_template_id">Template Penilaian <span class="text-danger">*</span></label>
                    <select id="appraisal_template_id" name="appraisal_template_id"
                            class="form-control{{ $errors->has('appraisal_template_id') ? ' is-invalid' : '' }}">
                        <option value="">-- Pilih Template --</option>
                        @foreach($templates as $tmpl)
                            <option value="{{ $tmpl->id }}"
                                    data-level="{{ $tmpl->level_id }}"
                                    data-default="{{ $tmpl->is_default ? '1' : '0' }}"
                                    {{ old('appraisal_template_id') == $tmpl->id ? 'selected' : '' }}>
                                {{ $tmpl->name }}
                                @if($tmpl->level) ({{ $tmpl->level->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('appraisal_template_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Template otomatis dipilih sesuai level jabatan karyawan.</small>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('appraisal.appraisals.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Mulai Penilaian</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
var levelTemplates = {};
$('#appraisal_template_id option').each(function() {
    var levelId   = $(this).data('level');
    var isDefault = $(this).data('default') == '1';
    var tmplId    = $(this).val();
    if (!tmplId || !levelId) return;
    if (!levelTemplates[levelId] || isDefault) {
        levelTemplates[levelId] = tmplId;
    }
});

$('#employee_id').on('change', function() {
    var levelId = $(this).find(':selected').data('level');
    if (levelId && levelTemplates[levelId]) {
        $('#appraisal_template_id').val(levelTemplates[levelId]);
    }
});

@if(auth()->user()->hasRole('karyawan'))
// Auto-select template sesuai level karyawan
$(document).ready(function() {
    var myLevelId = '{{ $employees->first()?->level_id }}';
    if (myLevelId && levelTemplates[myLevelId]) {
        $('#appraisal_template_id').val(levelTemplates[myLevelId]);
    }
});
@endif
</script>
@endsection
