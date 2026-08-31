@extends('layouts.grain')
@section('title', 'Isi Penilaian 360°')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('feedback360.mine') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-1">Penilaian untuk {{ $review->subject->name }}</div>
<p class="text-muted">Anda mengisi sebagai <strong>{{ \App\Models\Appraisal\Feedback360Review::$relationLabels[$review->relation_type] }}</strong> — {{ $review->cycle->title }}.
  Jawaban ini {{ $review->relation_type === 'self' ? 'akan terlihat sebagai penilaian diri sendiri' : 'anonim ke subjek, hanya rangkuman rata-rata yang ditampilkan' }}.</p>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('feedback360.submit', $review) }}">
      @csrf
      @foreach(\App\Models\Appraisal\Feedback360Review::$questions as $key => $label)
        @php $existing = $review->answers->firstWhere('question_key', $key); @endphp
        <div class="mb-4 pb-3 border-bottom">
          <label class="font-weight-bold small">{{ $label }} <span class="text-danger">*</span></label>
          <div class="d-flex" style="gap:1.5rem">
            @for($v = 1; $v <= 5; $v++)
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" class="custom-control-input" name="ratings[{{ $key }}]" id="{{ $key }}-{{ $v }}" value="{{ $v }}" required @checked($existing?->rating == $v)>
                <label class="custom-control-label" for="{{ $key }}-{{ $v }}">{{ $v }}</label>
              </div>
            @endfor
            <span class="small text-muted">(1 = perlu banyak perbaikan, 5 = sangat baik)</span>
          </div>
          <input type="text" name="comments[{{ $key }}]" class="form-control form-control-sm mt-2" value="{{ $existing?->comment }}" placeholder="Komentar (opsional)">
        </div>
      @endforeach
      <button class="btn btn-primary">Kirim Penilaian</button>
    </form>
  </div>
</div>
@endsection
