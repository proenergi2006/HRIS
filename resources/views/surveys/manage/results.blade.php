@extends('layouts.grain')
@section('title', 'Hasil Survey — ' . $survey->title)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('surveys.manage.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-1">{{ $survey->title }}</div>
<p class="text-muted small mb-4">{{ $respondentCount }} responden @if($survey->is_anonymous) (anonim) @endif</p>

@foreach($data as $qid => $d)
  <div class="card mb-3">
    <div class="card-header font-weight-bold">{{ $d['question']->text }}</div>
    <div class="card-body">
      @if($d['question']->type === 'scale')
        @if($d['avg'] !== null)
          <div class="h4">Rata-rata: {{ $d['avg'] }} / 10 <span class="small text-muted">({{ $d['count'] }} jawaban)</span></div>
          <div class="d-flex align-items-end" style="gap:4px;height:80px">
            @for($i = 0; $i <= 10; $i++)
              @php $c = $d['distribution'][$i] ?? 0; $max = $d['distribution']->max() ?: 1; @endphp
              <div style="flex:1;text-align:center">
                <div style="background:#2e6da4;height:{{ $max ? round($c/$max*60) : 0 }}px;border-radius:3px 3px 0 0"></div>
                <div class="small text-muted">{{ $i }}</div>
              </div>
            @endfor
          </div>
        @else
          <p class="text-muted small mb-0">Belum ada jawaban.</p>
        @endif
      @elseif(in_array($d['question']->type, ['single','multi']))
        @if($d['counts']->isEmpty())
          <p class="text-muted small mb-0">Belum ada jawaban.</p>
        @else
          @php $max = $d['counts']->max() ?: 1; @endphp
          @foreach($d['counts']->sortDesc() as $opt => $c)
            <div class="mb-2">
              <div class="d-flex justify-content-between small"><span>{{ $opt }}</span><span>{{ $c }}</span></div>
              <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ round($c/$max*100) }}%"></div></div>
            </div>
          @endforeach
        @endif
      @else
        @if($d['texts']->isEmpty())
          <p class="text-muted small mb-0">Belum ada jawaban.</p>
        @else
          <ul class="mb-0 small">
            @foreach($d['texts'] as $t)<li>{{ $t }}</li>@endforeach
          </ul>
        @endif
      @endif
    </div>
  </div>
@endforeach
@endsection
