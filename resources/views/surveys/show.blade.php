@extends('layouts.grain')
@section('title', $survey->title)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('surveys.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="card">
  <div class="card-body">
    <h3>{{ $survey->title }}</h3>
    @if($survey->is_anonymous)<p class="small text-muted"><i class="gd-shield mr-1"></i> Jawaban Anda anonim.</p>@endif
    <p class="text-muted">{{ $survey->description }}</p>

    <form method="POST" action="{{ route('surveys.submit', $survey) }}">
      @csrf
      @foreach($survey->questions as $q)
        <div class="form-group border-bottom pb-3 mb-3">
          <label class="font-weight-bold">{{ $loop->iteration }}. {{ $q->text }} @if($q->is_required)<span class="text-danger">*</span>@endif</label>

          @if($q->type === 'scale')
            <div class="d-flex" style="gap:.4rem;flex-wrap:wrap">
              @for($i = 0; $i <= 10; $i++)
                <label class="btn btn-outline-secondary btn-sm mb-0">
                  <input type="radio" name="answers[{{ $q->id }}]" value="{{ $i }}" class="d-none" {{ $q->is_required ? 'required' : '' }}> {{ $i }}
                </label>
              @endfor
            </div>
          @elseif($q->type === 'single')
            @foreach(($q->options ?? []) as $opt)
              <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="q{{ $q->id }}-{{ $loop->index }}" name="answers[{{ $q->id }}]" value="{{ $opt }}" {{ $q->is_required ? 'required' : '' }}>
                <label class="custom-control-label" for="q{{ $q->id }}-{{ $loop->index }}">{{ $opt }}</label>
              </div>
            @endforeach
          @elseif($q->type === 'multi')
            @foreach(($q->options ?? []) as $opt)
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="q{{ $q->id }}-{{ $loop->index }}" name="answers[{{ $q->id }}][]" value="{{ $opt }}">
                <label class="custom-control-label" for="q{{ $q->id }}-{{ $loop->index }}">{{ $opt }}</label>
              </div>
            @endforeach
          @else
            <textarea name="answers[{{ $q->id }}]" rows="3" class="form-control" {{ $q->is_required ? 'required' : '' }}></textarea>
          @endif
        </div>
      @endforeach

      <button type="submit" class="btn btn-primary">Kirim Jawaban</button>
    </form>
  </div>
</div>

<style>.btn-outline-secondary input:checked + * , label.btn input:checked ~ * {} label.btn input:checked { } label.btn:has(input:checked) { background:#0F2A4A;color:#fff }</style>
@endsection
