@extends('layouts.grain')
@section('title', $cycle->title)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.feedback360.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="d-flex justify-content-between align-items-start flex-wrap mb-4" style="gap:.5rem">
  <div>
    <div class="h3 mb-1">{{ $cycle->title }}</div>
    <div class="text-muted small">{{ $cycle->company?->short_name ?? $cycle->company?->name }} &middot;
      {{ $cycle->period_start->format('d/m/Y') }} – {{ $cycle->period_end->format('d/m/Y') }}
      &middot; <span class="badge badge-{{ \App\Models\Appraisal\Feedback360Cycle::$statusBadges[$cycle->status] }}">{{ \App\Models\Appraisal\Feedback360Cycle::$statusLabels[$cycle->status] }}</span>
    </div>
  </div>
  <div>
    @if($cycle->status === 'draft')
      <form method="POST" action="{{ route('appraisal.feedback360.open', $cycle) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Buka Cycle</button></form>
    @elseif($cycle->status === 'open')
      <form method="POST" action="{{ route('appraisal.feedback360.close', $cycle) }}" class="d-inline" onsubmit="return confirm('Tutup cycle? Rater tidak bisa isi lagi setelah ini.')">@csrf<button class="btn btn-sm btn-outline-danger">Tutup Cycle</button></form>
    @endif
  </div>
</div>

@if($cycle->status === 'draft')
<div class="card mb-4">
  <div class="card-header font-weight-bold">Tambah Subjek + Rater</div>
  <div class="card-body">
    <form method="POST" action="{{ route('appraisal.feedback360.add-subject', $cycle) }}">
      @csrf
      <div class="form-group">
        <label class="small">Karyawan yang Dinilai <span class="text-danger">*</span></label>
        <select name="subject_employee_id" class="form-control form-control-sm" required>
          <option value="">-- Pilih --</option>
          @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach
        </select>
        <small class="form-text text-muted">Penilaian diri sendiri (self) otomatis ditambahkan.</small>
      </div>
      <label class="small font-weight-bold">Rater Tambahan (atasan / rekan / bawahan)</label>
      @for($i = 0; $i < 5; $i++)
        <div class="form-row">
          <div class="form-group col-md-7 mb-2">
            <select name="rater_employee_ids[]" class="form-control form-control-sm">
              <option value="">-- (kosongkan kalau tak dipakai) --</option>
              @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach
            </select>
          </div>
          <div class="form-group col-md-5 mb-2">
            <select name="relation_types[]" class="form-control form-control-sm">
              @foreach(\App\Models\Appraisal\Feedback360Review::$relationLabels as $k => $l)
                @continue($k === 'self')
                <option value="{{ $k }}">{{ $l }}</option>
              @endforeach
            </select>
          </div>
        </div>
      @endfor
      <button class="btn btn-sm btn-primary mt-2">Tambahkan</button>
    </form>
  </div>
</div>
@endif

<div class="card">
  <div class="card-header font-weight-bold">Subjek &amp; Rater ({{ $bySubject->count() }} orang dinilai)</div>
  <div class="card-body p-0">
    @forelse($bySubject as $subjectId => $reviews)
      @php $subject = $reviews->first()->subject; @endphp
      <div class="border-bottom p-3">
        <div class="d-flex justify-content-between align-items-center">
          <span class="font-weight-bold">{{ $subject->name }}</span>
          <a href="{{ route('appraisal.feedback360.results', [$cycle, $subject]) }}" class="btn btn-xs btn-outline-info">Lihat Hasil</a>
        </div>
        <div class="d-flex flex-wrap mt-2" style="gap:.4rem">
          @foreach($reviews as $r)
            <span class="badge badge-light border p-2">
              {{ $r->rater->name }} <span class="text-muted">({{ \App\Models\Appraisal\Feedback360Review::$relationLabels[$r->relation_type] }})</span>
              <span class="badge badge-{{ $r->isSubmitted() ? 'success' : 'secondary' }} ml-1">{{ $r->isSubmitted() ? 'Terkirim' : 'Menunggu' }}</span>
              @if($cycle->status === 'draft' && $r->relation_type !== 'self')
                <form method="POST" action="{{ route('appraisal.feedback360.remove-review', [$cycle, $r]) }}" class="d-inline" onsubmit="return confirm('Hapus rater ini?')">
                  @csrf @method('DELETE')<button class="btn btn-link btn-sm p-0 ml-1 text-danger" style="line-height:1">&times;</button>
                </form>
              @endif
            </span>
          @endforeach
        </div>
      </div>
    @empty
      <p class="text-muted small p-3 mb-0">Belum ada subjek. Tambahkan lewat form di atas.</p>
    @endforelse
  </div>
</div>
@endsection
