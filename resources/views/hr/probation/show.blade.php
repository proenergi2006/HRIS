@extends('layouts.grain')
@section('title', 'Probation Review — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.probation.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header font-weight-bold">{{ $employee->name }}</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-5 text-muted">Jabatan</dt><dd class="col-sm-7">{{ $employee->position?->name ?? '—' }}</dd>
          <dt class="col-sm-5 text-muted">Departemen</dt><dd class="col-sm-7">{{ $employee->department?->name ?? '—' }}</dd>
          <dt class="col-sm-5 text-muted">Mulai Kerja</dt><dd class="col-sm-7">{{ $employee->start_date?->format('d/m/Y') ?? '—' }}</dd>
          <dt class="col-sm-5 text-muted">Probation Berakhir</dt><dd class="col-sm-7">{{ $contract?->end_date?->format('d/m/Y') ?? '— belum ada kontrak probation' }}</dd>
        </dl>
      </div>
    </div>

    <div class="card">
      <div class="card-header font-weight-bold">Evaluasi Baru</div>
      <div class="card-body">
        <form method="POST" action="{{ route('hr.probation.store', $employee) }}">
          @csrf
          <div class="form-group">
            <label class="small font-weight-bold">Keputusan <span class="text-danger">*</span></label>
            <select name="decision" id="decision" class="form-control" required onchange="document.getElementById('extend-wrap').style.display = this.value === 'extended' ? '' : 'none'">
              <option value="">— pilih —</option>
              @foreach(\App\Models\HR\ProbationReview::$decisionLabels as $k => $lbl)
                <option value="{{ $k }}">{{ $lbl }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group" id="extend-wrap" style="display:none">
            <label class="small font-weight-bold">Perpanjang Sampai <span class="text-danger">*</span></label>
            <input type="date" name="extended_until" class="form-control">
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Catatan Kinerja</label>
            <textarea name="performance_notes" rows="4" class="form-control" placeholder="Ringkasan kinerja selama masa probation..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary" onclick="return confirm('Simpan hasil evaluasi ini? Status kepegawaian karyawan bisa berubah otomatis.')">Simpan Evaluasi</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header font-weight-bold">Riwayat Evaluasi ({{ $reviews->count() }})</div>
      <div class="card-body p-0">
        @forelse($reviews as $r)
          <div class="px-3 py-3 border-bottom">
            <div class="d-flex justify-content-between">
              <span class="badge badge-{{ \App\Models\HR\ProbationReview::$decisionBadges[$r->decision] }}">{{ \App\Models\HR\ProbationReview::$decisionLabels[$r->decision] }}</span>
              <span class="small text-muted">{{ $r->review_date->format('d/m/Y') }} — {{ $r->reviewedBy?->name ?? '-' }}</span>
            </div>
            @if($r->performance_notes)<div class="small mt-2">{{ $r->performance_notes }}</div>@endif
            @if($r->extended_until)<div class="small text-muted mt-1">Diperpanjang sampai {{ $r->extended_until->format('d/m/Y') }}</div>@endif
          </div>
        @empty
          <div class="text-muted small p-4 text-center">Belum ada evaluasi.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
