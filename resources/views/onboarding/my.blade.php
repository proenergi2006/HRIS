@extends('layouts.grain')
@section('title', 'Onboarding Saya')

@section('content')
@include('components.notification')

@php
  $total = $tasks->count();
  $done  = $tasks->where('is_done', true)->count();
  $pct   = $total ? (int) round($done / $total * 100) : 0;
  $indTasks = $tasks->filter(fn ($t) => $t->item->category === 'induction');
  $otherTasks = $tasks->filter(fn ($t) => $t->item->category !== 'induction');
@endphp

<div class="h3 mb-1">Selamat Datang, {{ $employee->name }} 👋</div>
<p class="text-muted">Lengkapi materi orientasi berikut. Klik setiap materi, baca, lalu tandai <strong>sudah saya pahami</strong>.</p>

<div class="card mb-4">
  <div class="card-body py-3">
    <div class="d-flex justify-content-between small mb-1">
      <span class="font-weight-bold">Progres Onboarding</span><span>{{ $done }}/{{ $total }} selesai</span>
    </div>
    <div class="progress" style="height:10px">
      <div class="progress-bar {{ $pct == 100 ? 'bg-success' : 'bg-primary' }}" style="width:{{ $pct }}%"></div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Materi Orientasi &amp; Induction</div>
  <div class="card-body">
    @forelse($indTasks->sortBy('item.sort_order') as $task)
      @php $item = $task->item; $fakta = \Illuminate\Support\Str::contains(strtolower($item->label), 'integritas'); @endphp
      <div class="border rounded p-3 mb-3 {{ $task->is_done ? 'bg-light' : '' }}">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="font-weight-bold">{{ $item->label }}</span>
            @if($item->is_required)<span class="badge badge-light border ml-1">wajib</span>@endif
            @if($task->is_done)<span class="badge badge-success ml-1"><i class="gd-check"></i> dipahami</span>@endif
          </div>
          @if($item->hasMaterial())
            <a href="{{ $item->material_url ?: route('onboarding.material', $item) }}"
               target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary">
              <i class="gd-file mr-1"></i> Buka Materi
            </a>
          @endif
        </div>
        @if($item->description)<p class="small text-muted mt-2 mb-2">{!! nl2br(e($item->description)) !!}</p>@endif

        @if($task->is_done)
          <div class="small text-success">
            Dikonfirmasi {{ $task->acknowledged_at?->format('d/m/Y H:i') ?? $task->done_at?->format('d/m/Y H:i') }}
            @if($task->acknowledgement_note) — "{{ $task->acknowledgement_note }}"@endif
          </div>
        @else
          <form method="POST" action="{{ route('onboarding.acknowledge', $task) }}" class="mt-2">
            @csrf
            @if($fakta)
              <div class="alert alert-warning py-2 px-3 small mb-2">
                Dengan menyetujui, saya menyatakan telah membaca, memahami, dan bersedia mematuhi
                <strong>Pakta / Fakta Integritas</strong> perusahaan, termasuk ketentuan benturan
                kepentingan serta larangan suap dan gratifikasi.
              </div>
            @endif
            <div class="form-row align-items-end">
              @if($fakta)
                <div class="form-group col-md-4 mb-1">
                  <input type="text" name="note" class="form-control form-control-sm" placeholder="Ketik nama lengkap Anda" required>
                </div>
              @endif
              <div class="form-group col-auto mb-1">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" id="agree-{{ $task->id }}" name="agree" value="1" required>
                  <label class="custom-control-label small" for="agree-{{ $task->id }}">
                    Saya sudah membaca &amp; {{ $fakta ? 'menyetujui' : 'memahami' }} materi ini
                  </label>
                </div>
              </div>
              <div class="form-group col-auto mb-1">
                <button class="btn btn-sm btn-success">Konfirmasi</button>
              </div>
            </div>
          </form>
        @endif
      </div>
    @empty
      <p class="text-muted small mb-0">Belum ada materi induction. Hubungi HR.</p>
    @endforelse
  </div>
</div>

@if($otherTasks->isNotEmpty())
<div class="card">
  <div class="card-header font-weight-bold">Item Lain (ditangani HR/GA)</div>
  <div class="card-body">
    <ul class="list-unstyled mb-0">
      @foreach($otherTasks->sortBy('item.sort_order') as $task)
        <li class="d-flex align-items-center py-1">
          <i class="gd-{{ $task->is_done ? 'check text-success' : 'time text-muted' }} mr-2"></i>
          <span class="{{ $task->is_done ? 'text-muted' : '' }} small">{{ $task->item->label }}</span>
          <span class="badge badge-light border ml-2 small">{{ \App\Models\OnboardingChecklistItem::$categoryLabels[$task->item->category] ?? $task->item->category }}</span>
        </li>
      @endforeach
    </ul>
  </div>
</div>
@endif
@endsection
