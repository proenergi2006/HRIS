@extends('layouts.grain')
@section('title', 'Peserta Training')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Training &amp; Development</div>
  @can('training.edit')
  <a href="{{ route('training.programs.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-settings mr-1"></i> Kelola Program
  </a>
  @endcan
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">Program</label>
        <select name="training_program_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach($programs as $p)
            <option value="{{ $p->id }}" @selected(request('training_program_id') == $p->id)>{{ $p->title }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Status</label>
        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach(\App\Models\TrainingParticipant::$statusLabels as $key => $label)
            <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Tambah Peserta</div>
  <div class="card-body">
    <form method="POST" action="{{ route('training.participants.store') }}" class="form-row align-items-end mb-4">
      @csrf
      <div class="form-group col-md-3 mb-2">
        <select name="employee_id" class="form-control form-control-sm" required>
          <option value="">-- Karyawan --</option>
          @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-2">
        <select name="training_program_id" class="form-control form-control-sm" required>
          <option value="">-- Program --</option>
          @foreach($programs as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-2">
        <input type="date" name="start_date" class="form-control form-control-sm" required>
      </div>
      <div class="form-group col-md-2 mb-2">
        <input type="date" name="end_date" class="form-control form-control-sm">
      </div>
      <div class="form-group col-md-1 mb-2">
        <select name="status" class="form-control form-control-sm">
          @foreach(\App\Models\TrainingParticipant::$statusLabels as $key => $label)
            <option value="{{ $key }}" @selected($key === 'planned')>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-1 mb-2">
        <button type="submit" class="btn btn-sm btn-primary btn-block">+</button>
      </div>
    </form>

    @if($participants->isEmpty())
      <p class="text-muted small mb-0">Belum ada data peserta training.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Karyawan</th><th>Program</th><th>Periode</th><th>Status</th><th>Nilai/Sertifikat</th><th></th></tr>
          </thead>
          <tbody>
          @foreach($participants as $tp)
            @php $f = 'tpp-form-' . $tp->id; @endphp
            <tr>
              <td>{{ $tp->employee?->name }}</td>
              <td>{{ $tp->program?->title }}</td>
              <td class="small">{{ $tp->start_date?->format('d/m/Y') }} @if($tp->end_date) – {{ $tp->end_date->format('d/m/Y') }}@endif</td>
              <td style="width:140px">
                <select name="status" form="{{ $f }}" class="form-control form-control-sm">
                  @foreach(\App\Models\TrainingParticipant::$statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected($tp->status === $key)>{{ $label }}</option>
                  @endforeach
                </select>
              </td>
              <td style="width:160px">
                <input type="text" name="score" form="{{ $f }}" value="{{ $tp->score }}" class="form-control form-control-sm mb-1" placeholder="Nilai">
                <input type="text" name="certificate_number" form="{{ $f }}" value="{{ $tp->certificate_number }}" class="form-control form-control-sm" placeholder="No. Sertifikat">
              </td>
              <td style="width:80px;white-space:nowrap">
                <input type="hidden" name="employee_id" form="{{ $f }}" value="{{ $tp->employee_id }}">
                <input type="hidden" name="training_program_id" form="{{ $f }}" value="{{ $tp->training_program_id }}">
                <input type="hidden" name="start_date" form="{{ $f }}" value="{{ $tp->start_date?->format('Y-m-d') }}">
                <input type="hidden" name="end_date" form="{{ $f }}" value="{{ $tp->end_date?->format('Y-m-d') }}">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus data peserta ini?" data-confirm-title="Hapus Peserta" data-form="tpp-del-{{ $tp->id }}"><i class="gd-trash"></i></a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($participants as $tp)
        <form id="tpp-form-{{ $tp->id }}" method="POST" action="{{ route('training.participants.update', $tp) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="tpp-del-{{ $tp->id }}" method="POST" action="{{ route('training.participants.destroy', $tp) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
