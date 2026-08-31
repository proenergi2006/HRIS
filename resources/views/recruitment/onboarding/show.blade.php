@extends('layouts.grain')
@section('title', 'Onboarding — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('recruitment.onboarding.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-4">Checklist Onboarding — {{ $employee->name }}</div>

@foreach(\App\Models\OnboardingChecklistItem::$categoryLabels as $catKey => $catLabel)
  @php $catTasks = $tasks->filter(fn($t) => $t->item->category === $catKey); @endphp
  @continue($catTasks->isEmpty())
  <div class="card mb-3">
    <div class="card-header font-weight-bold">{{ $catLabel }}</div>
    <div class="card-body">
      @foreach($catTasks as $task)
        <form method="POST" action="{{ route('recruitment.onboarding.tasks.toggle', [$employee, $task]) }}"
              class="d-flex justify-content-between align-items-center border-bottom py-2">
          @csrf
          <div>
            <span class="{{ $task->is_done ? 'text-muted' : '' }}" style="{{ $task->is_done ? 'text-decoration:line-through' : '' }}">
              {{ $task->item->label }}
            </span>
            @if($task->item->is_required)<span class="badge badge-light border ml-1" style="font-size:.65rem">wajib</span>@endif
            @if($task->is_done)
              <div class="small text-muted">Selesai {{ $task->done_at?->format('d/m/Y H:i') }} · {{ $task->doneBy?->name }}</div>
            @endif
          </div>
          <button type="submit" class="btn btn-sm {{ $task->is_done ? 'btn-outline-secondary' : 'btn-success' }}">
            {{ $task->is_done ? 'Batalkan' : 'Tandai Selesai' }}
          </button>
        </form>
      @endforeach
    </div>
  </div>
@endforeach
@endsection
