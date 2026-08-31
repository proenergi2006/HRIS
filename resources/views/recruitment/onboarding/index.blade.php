@extends('layouts.grain')
@section('title', 'Onboarding')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Onboarding Karyawan Baru</div>
  @can('recruitment.edit')
  <a href="{{ route('recruitment.onboarding.templates') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-settings mr-1"></i> Template Checklist
  </a>
  @endcan
</div>

<div class="alert alert-info py-2 px-3" style="font-size:.85rem">
  <i class="gd-info mr-1"></i> Checklist onboarding otomatis dibuat saat kandidat dikonversi jadi karyawan (Rekrutmen &gt; Kandidat).
  Untuk karyawan yang dikonversi <strong>sebelum</strong> checklist disiapkan, atau yang ditambahkan langsung lewat Data Karyawan,
  mulai onboarding-nya manual di bawah.
</div>

{{-- ── Karyawan baru yang onboarding-nya belum dimulai ── --}}
@can('recruitment.edit')
@if($pending->isNotEmpty())
<div class="card mb-3 border-warning">
  <div class="card-header bg-warning font-weight-bold">
    <i class="gd-time mr-1"></i> Karyawan Baru — Onboarding Belum Dimulai ({{ $pending->count() }})
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th class="pl-3">Nama</th><th>Jabatan</th><th>Departemen</th><th class="text-center">Tgl. Masuk</th><th></th></tr></thead>
        <tbody>
        @foreach($pending as $e)
          <tr>
            <td class="pl-3 font-weight-bold">{{ $e->name }}
              @if($e->employment_status === 'probation')<span class="badge badge-info ml-1">Probation</span>@endif
            </td>
            <td>{{ $e->position?->name ?? '—' }}</td>
            <td>{{ $e->department?->name ?? '—' }}</td>
            <td class="text-center">{{ optional($e->start_date)->format('d/m/Y') ?? '—' }}</td>
            <td class="text-right pr-3">
              <form method="POST" action="{{ route('recruitment.onboarding.start', $e) }}" class="d-inline">
                @csrf
                <button class="btn btn-xs btn-primary"><i class="gd-plus mr-1"></i>Mulai Onboarding</button>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

{{-- Mulai manual untuk karyawan lain (di luar daftar di atas) --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="POST" action="{{ url('recruitment/onboarding') }}/_/start" class="form-row align-items-end mb-0"
          onsubmit="var v=this.querySelector('#start-emp').value; if(!v){return false;} this.action=this.action.replace('/_/start', '/'+v+'/start');">
      @csrf
      <div class="form-group col-md-5 mb-0">
        <label class="small font-weight-bold">Mulai Onboarding Manual — Karyawan</label>
        <select id="start-emp" class="form-control form-control-sm">
          <option value="">— pilih karyawan —</option>
          @foreach(\App\Models\Employee::where('is_active', true)->whereDoesntHave('onboardingTasks')->orderBy('name')->get() as $emp)
            <option value="{{ $emp->getRouteKey() }}">{{ $emp->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0"><button class="btn btn-outline-primary btn-sm">Mulai</button></div>
    </form>
  </div>
</div>
@endcan

{{-- ── Progress onboarding berjalan ── --}}
<div class="card">
  <div class="card-header font-weight-bold">Progress Onboarding</div>
  <div class="card-body">
    @if($employees->isEmpty())
      <p class="text-muted small mb-0">Belum ada karyawan dengan task onboarding.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Karyawan</th><th>Progress</th><th></th></tr></thead>
          <tbody>
          @foreach($employees as $e)
            @php $pct = $e->onboarding_tasks_count > 0 ? round($e->done_tasks_count / $e->onboarding_tasks_count * 100) : 0; @endphp
            <tr>
              <td>{{ $e->name }}</td>
              <td style="min-width:200px">
                <div class="progress" style="height:8px">
                  <div class="progress-bar {{ $pct == 100 ? 'bg-success' : 'bg-warning' }}" style="width:{{ $pct }}%"></div>
                </div>
                <div class="small text-muted">{{ $e->done_tasks_count }}/{{ $e->onboarding_tasks_count }} selesai</div>
              </td>
              <td><a href="{{ route('recruitment.onboarding.show', $e) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
