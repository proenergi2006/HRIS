@extends('layouts.grain')
@section('title', 'Hasil 360° — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.feedback360.show', $cycle) }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-1">Hasil 360° — {{ $employee->name }}</div>
<p class="text-muted">{{ $cycle->title }}</p>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Rata-rata per Kompetensi (semua rater)</div>
  <div class="card-body">
    @foreach(\App\Models\Appraisal\Feedback360Review::$questions as $key => $label)
      @php $avg = $questionAverages[$key]; @endphp
      <div class="mb-3">
        <div class="d-flex justify-content-between small">
          <span>{{ $label }}</span><span class="font-weight-bold">{{ $avg !== null ? $avg . ' / 5' : '—' }}</span>
        </div>
        <div class="progress" style="height:8px">
          <div class="progress-bar bg-primary" style="width:{{ $avg !== null ? ($avg/5*100) : 0 }}%"></div>
        </div>
      </div>
    @endforeach
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Per Rater</div>
  <div class="card-body p-0">
    @foreach(\App\Models\Appraisal\Feedback360Review::$relationLabels as $relKey => $relLabel)
      @php $group = $byRelation->get($relKey, collect()); @endphp
      @continue($group->isEmpty())
      <div class="border-bottom p-3">
        <div class="font-weight-bold small mb-2">{{ $relLabel }} ({{ $group->count() }})</div>
        @foreach($group as $r)
          <div class="mb-2 pl-2" style="border-left:3px solid #e9ecef">
            <div class="small text-muted">{{ $relKey === 'self' ? 'Diri sendiri' : $r->rater->name }} — rata-rata {{ $r->averageRating() ?? '—' }}/5</div>
            @foreach($r->answers as $a)
              @if($a->comment)<div class="small font-italic">"{{ $a->comment }}" <span class="text-muted">({{ \App\Models\Appraisal\Feedback360Review::$questions[$a->question_key] ?? $a->question_key }})</span></div>@endif
            @endforeach
          </div>
        @endforeach
      </div>
    @endforeach
  </div>
</div>
@endsection
