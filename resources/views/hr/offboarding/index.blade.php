@extends('layouts.grain')
@section('title', 'Clearance Resign')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Clearance Resign (Offboarding)</div>
  <a href="{{ route('hr.offboarding.templates') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-settings mr-1"></i> Template Checklist
  </a>
</div>

<div class="alert alert-info py-2 px-3" style="font-size:.85rem">
  <i class="gd-info mr-1"></i> Checklist otomatis dibuat saat pengajuan Termination dibuat (Approval Engine &gt; Termination).
  Bisa juga dimulai manual di bawah.
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="POST" action="{{ route('hr.offboarding.start', ['employee' => 0]) }}" id="start-form" class="form-row align-items-end mb-0"
          onsubmit="this.action = this.action.replace('/0', '/' + document.getElementById('start-emp').value); return document.getElementById('start-emp').value !== '';">
      @csrf
      <div class="form-group col-md-5 mb-0">
        <label class="small font-weight-bold">Mulai Clearance Manual — Karyawan</label>
        <select id="start-emp" class="form-control form-control-sm">
          <option value="">— pilih karyawan —</option>
          @foreach(\App\Models\Employee::where('is_active', true)->orderBy('name')->get(['id','name']) as $emp)
            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0"><button class="btn btn-outline-primary btn-sm">Mulai</button></div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if($employees->isEmpty())
      <p class="text-muted small mb-0">Belum ada karyawan dengan proses clearance resign.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Karyawan</th><th>Progress</th><th></th></tr></thead>
          <tbody>
          @foreach($employees as $e)
            @php $pct = $e->offboarding_tasks_count > 0 ? round($e->done_tasks_count / $e->offboarding_tasks_count * 100) : 0; @endphp
            <tr>
              <td>{{ $e->name }}</td>
              <td style="min-width:200px">
                <div class="progress" style="height:8px">
                  <div class="progress-bar {{ $pct == 100 ? 'bg-success' : 'bg-warning' }}" style="width:{{ $pct }}%"></div>
                </div>
                <div class="small text-muted">{{ $e->done_tasks_count }}/{{ $e->offboarding_tasks_count }} selesai</div>
              </td>
              <td><a href="{{ route('hr.offboarding.show', $e) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
