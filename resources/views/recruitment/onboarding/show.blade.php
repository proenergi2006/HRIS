@extends('layouts.grain')
@section('title', 'Onboarding — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('recruitment.onboarding.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-1">Checklist Onboarding — {{ $employee->name }}</div>
@php $done = $tasks->where('is_done', true)->count(); $tot = $tasks->count(); @endphp
<p class="text-muted">{{ $done }}/{{ $tot }} selesai. Item <span class="badge badge-info">Induction</span> dikonfirmasi sendiri oleh karyawan lewat menu <em>Onboarding Saya</em>.</p>

@foreach(\App\Models\OnboardingChecklistItem::$categoryLabels as $catKey => $catLabel)
  @php $catTasks = $tasks->filter(fn($t) => $t->item->category === $catKey); @endphp
  @continue($catTasks->isEmpty())
  <div class="card mb-3">
    <div class="card-header font-weight-bold">{{ $catLabel }}</div>
    <div class="card-body">
      @foreach($catTasks as $task)
        @php $ack = $task->item->requires_acknowledgement; @endphp
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
          <div>
            <span class="{{ $task->is_done ? 'text-muted' : '' }}" style="{{ $task->is_done ? 'text-decoration:line-through' : '' }}">
              {{ $task->item->label }}
            </span>
            @if($task->item->is_required)<span class="badge badge-light border ml-1" style="font-size:.65rem">wajib</span>@endif
            @if($ack)<span class="badge badge-info ml-1" style="font-size:.65rem">konfirmasi karyawan</span>@endif
            @if($task->item->hasMaterial())
              <a href="{{ $task->item->material_url ?: route('onboarding.material', $task->item) }}" target="_blank" rel="noopener" class="small ml-1"><i class="gd-file"></i> materi</a>
            @endif
            @if($task->is_done)
              <div class="small text-muted">
                {{ $ack ? 'Dikonfirmasi' : 'Selesai' }} {{ ($task->acknowledged_at ?? $task->done_at)?->format('d/m/Y H:i') }} · {{ $task->doneBy?->name }}
                @if($task->acknowledgement_note) — "{{ $task->acknowledgement_note }}"@endif
              </div>
            @endif
          </div>
          @if($ack)
            <span class="badge badge-{{ $task->is_done ? 'success' : 'secondary' }}">{{ $task->is_done ? 'Sudah dikonfirmasi' : 'Menunggu karyawan' }}</span>
          @else
            <form method="POST" action="{{ route('recruitment.onboarding.tasks.toggle', [$employee, $task]) }}">
              @csrf
              <button type="submit" class="btn btn-sm {{ $task->is_done ? 'btn-outline-secondary' : 'btn-success' }}">
                {{ $task->is_done ? 'Batalkan' : 'Tandai Selesai' }}
              </button>
            </form>
          @endif
        </div>
      @endforeach
    </div>
  </div>
@endforeach
@endsection
