@extends('layouts.grain')
@section('title', 'Detail Penilaian')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.appraisals.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header font-weight-bold d-flex justify-content-between">
        <span>{{ $appraisal->employee->name }} — {{ $appraisal->period->name }}</span>
        <span class="badge badge-{{ \App\Models\Appraisal\Appraisal::$statusBadges[$appraisal->status] ?? 'secondary' }}">
          {{ $appraisal->status_label }}
        </span>
      </div>
      <div class="card-body">
        <dl class="row mb-3">
          <dt class="col-sm-4 text-muted">Jabatan</dt><dd class="col-sm-8">{{ $appraisal->employee->position?->name ?? '-' }}</dd>
          <dt class="col-sm-4 text-muted">Departemen</dt><dd class="col-sm-8">{{ $appraisal->employee->department?->name ?? '-' }}</dd>
          <dt class="col-sm-4 text-muted">Evaluator</dt><dd class="col-sm-8">{{ $appraisal->evaluator?->name ?? '-' }}</dd>
          @if($appraisal->template)
          <dt class="col-sm-4 text-muted">Template</dt><dd class="col-sm-8">{{ $appraisal->template->name }}</dd>
          @endif
        </dl>

        <div class="table-responsive mb-3">
          <table class="table table-bordered table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th style="width:32px">#</th><th>KPI / Objective</th><th style="width:110px">Kategori</th>
                <th style="width:60px">Bobot</th><th>Target</th><th>Realisasi</th>
                <th style="width:80px">Capaian</th><th style="width:70px">Skor</th>
              </tr>
            </thead>
            <tbody>
            @forelse($appraisal->objectives as $i => $obj)
              <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $obj->title }}</td>
                <td>{{ $obj->category ?? '-' }}</td>
                <td class="text-center">{{ $obj->weight_pct }}%</td>
                <td>{{ $obj->target ?? '-' }}</td>
                <td>{{ $obj->actual ?? '-' }}</td>
                <td class="text-center">{{ $obj->achievement_pct !== null ? number_format($obj->achievement_pct, 1).'%' : '-' }}</td>
                <td class="text-center font-weight-bold">{{ $obj->score ?? '-' }}</td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-muted py-3">Belum ada KPI diisi.</td></tr>
            @endforelse
            </tbody>
            <tfoot class="thead-light">
              <tr>
                <td colspan="7" class="text-right font-weight-bold">Total Skor</td>
                <td class="text-center font-weight-bold">{{ number_format((float) $appraisal->total_score, 2) }}</td>
              </tr>
              @if($appraisal->grade)
              <tr>
                <td colspan="7" class="text-right font-weight-bold">Grade</td>
                <td class="text-center"><span class="badge badge-info">{{ $appraisal->grade }}</span></td>
              </tr>
              @endif
            </tfoot>
          </table>
        </div>

        @if($appraisal->strengths)
          <div class="mb-2"><div class="text-muted small font-weight-bold mb-1">Kekuatan</div><p class="mb-0">{{ $appraisal->strengths }}</p></div>
        @endif
        @if($appraisal->development_notes)
          <div class="mb-2"><div class="text-muted small font-weight-bold mb-1">Area Pengembangan</div><p class="mb-0">{{ $appraisal->development_notes }}</p></div>
        @endif
        @if($appraisal->notes)
          <div class="mb-2"><div class="text-muted small font-weight-bold mb-1">Catatan Evaluator</div><p class="border rounded p-2 bg-light mb-0">{{ $appraisal->notes }}</p></div>
        @endif

        @if($appraisal->isDraft())
        <hr>
        <form method="POST" action="{{ route('appraisal.appraisals.submit', $appraisal) }}">
          @csrf
          <button type="button" class="btn btn-success"
              data-confirm="Submit penilaian {{ $appraisal->employee->name }} untuk persetujuan?"
              data-confirm-title="Submit Penilaian" data-confirm-type="primary" data-confirm-ok="Ya, Submit"
              data-form="form-submit-{{ $appraisal->id }}">
              <i class="gd-arrow-right mr-1"></i> Submit untuk Persetujuan
          </button>
          <a href="{{ route('appraisal.appraisals.edit', $appraisal) }}" class="btn btn-outline-primary">
              <i class="gd-pencil mr-1"></i> Edit
          </a>
        </form>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-3">
          <a href="{{ route('appraisal.appraisals.pdf', $appraisal) }}" class="btn btn-outline-secondary" target="_blank">
            <i class="gd-download mr-1"></i> Download PDF
          </a>
          @if(in_array($appraisal->status, ['draft','rejected']))
          <form method="POST" action="{{ route('appraisal.appraisals.destroy', $appraisal) }}" onsubmit="return confirm('Hapus penilaian ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Hapus</button>
          </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header font-weight-bold">Alur Persetujuan</div>
      <div class="card-body">
        @php $ar = $appraisal->approvalRequest; @endphp
        @if(!$ar)
          <p class="text-muted small mb-0">Belum diajukan.</p>
        @else
          <ol class="list-unstyled mb-0">
            @foreach($ar->steps as $s)
              <li class="d-flex mb-3">
                <span class="mr-3">
                  @if($s->status === 'approved')<i class="gd-check text-success"></i>
                  @elseif($s->status === 'rejected')<i class="gd-close text-danger"></i>
                  @elseif($s->status === 'skipped')<i class="gd-minus text-muted"></i>
                  @else<i class="gd-time text-warning"></i>@endif
                </span>
                <div>
                  <div class="font-weight-bold small">Step {{ $s->step_order }} — {{ $s->approver_label }}</div>
                  <div class="small text-muted">{{ $s->approver?->name ?? '—' }}
                    @if($s->acted_at) · {{ ucfirst($s->status) }} {{ $s->acted_at->format('d/m/Y H:i') }}@endif</div>
                  @if($s->notes)<div class="small font-italic">"{{ $s->notes }}"</div>@endif
                </div>
              </li>
            @endforeach
          </ol>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
