@extends('layouts.grain')
@section('title', 'Career — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('career.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-5">
    <div class="card mb-4">
      <div class="card-header font-weight-bold">{{ $employee->name }}</div>
      <div class="card-body">
        <dl class="row mb-3">
          <dt class="col-sm-5 text-muted">Jabatan Sekarang</dt><dd class="col-sm-7">{{ $employee->position?->name ?? '—' }}</dd>
          <dt class="col-sm-5 text-muted">Career Path</dt><dd class="col-sm-7">{{ $employee->careerPath?->title ?? '—' }}</dd>
          @if($currentStep)
            <dt class="col-sm-5 text-muted">Jenjang Saat Ini</dt><dd class="col-sm-7">Step {{ $currentStep->step_order }}</dd>
            <dt class="col-sm-5 text-muted">Jenjang Berikutnya</dt>
            <dd class="col-sm-7">{{ $nextStep?->position?->name ?? 'Sudah di jenjang tertinggi' }}</dd>
          @elseif($employee->careerPath)
            <dt class="col-sm-5 text-muted">Posisi di Path</dt><dd class="col-sm-7 text-muted">Jabatan sekarang belum ada di career path ini.</dd>
          @endif
        </dl>

        @can('career.edit')
        <form method="POST" action="{{ route('career.assign-path', $employee) }}" class="form-row align-items-end">
          @csrf
          <div class="form-group col-md-8 mb-2">
            <label class="small">Assign Career Path</label>
            <select name="career_path_id" class="form-control form-control-sm">
              <option value="">-- Tidak ada --</option>
              @foreach($careerPaths as $cp)
                <option value="{{ $cp->id }}" @selected($employee->career_path_id === $cp->id)>{{ $cp->title }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4 mb-2">
            <button type="submit" class="btn btn-sm btn-primary btn-block">Simpan</button>
          </div>
        </form>
        @endcan

        @can('hr-request.create')
        <a href="{{ route('approval.hr-request.create', 'promotion-rotation') }}" class="btn btn-sm btn-outline-success mt-2">
          <i class="gd-stats-up mr-1"></i> Ajukan Promosi / Rotasi
        </a>
        @endcan
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header font-weight-bold">Riwayat Jabatan &amp; Organisasi</div>
      <div class="card-body">
        @if($employee->orgExperiences->isEmpty())
          <p class="text-muted small mb-0">Belum ada riwayat jabatan/organisasi tercatat.</p>
        @else
          <ol class="list-unstyled mb-0">
            @foreach($employee->orgExperiences as $exp)
              <li class="d-flex mb-3">
                <span class="mr-3"><i class="gd-time text-info"></i></span>
                <div>
                  <div class="font-weight-bold small">{{ $exp->change_type_label }} — {{ $exp->position_name ?? $exp->position?->name ?? '—' }}</div>
                  <div class="small text-muted">
                    {{ $exp->start_date?->format('d/m/Y') }}
                    @if($exp->end_date) – {{ $exp->end_date->format('d/m/Y') }}@endif
                    @if($exp->unit_name) · {{ $exp->unit_name }} @endif
                  </div>
                  @if($exp->remarks)<div class="small font-italic">{{ $exp->remarks }}</div>@endif
                </div>
              </li>
            @endforeach
          </ol>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
