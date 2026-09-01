@extends('layouts.grain')
@section('title', 'Probation Review')

@section('content')
@include('components.notification')

<div class="h3 mb-3">Probation Review</div>

<form method="GET" class="form-inline mb-3">
  <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="">— Konsolidasi Grup —</option>
    @foreach($companies as $c)
      <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>
    @endforeach
  </select>
</form>

<div class="alert alert-info small mb-4">
  Karyawan berstatus <strong>Probation</strong>, diurutkan dari yang masa probation-nya paling
  cepat berakhir. Evaluasi bisa diisi kapan saja — hasilnya langsung mengubah status kepegawaian
  (Lulus → Permanen) atau memperpanjang tanggal berakhir kontrak probation.
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Nama</th><th>Jabatan</th><th>Mulai Kerja</th><th>Probation Berakhir</th><th>Sisa Hari</th><th>Review Terakhir</th><th style="width:100px"></th></tr></thead>
        <tbody>
        @forelse($employees as $r)
          @php $e = $r['employee']; $days = $r['daysLeft']; @endphp
          <tr class="{{ $days !== null && $days <= 14 ? 'table-warning' : '' }}">
            <td class="font-weight-bold">{{ $e->name }}</td>
            <td class="small text-muted">{{ $e->position?->name ?? '—' }}</td>
            <td class="small">{{ $e->start_date?->format('d/m/Y') ?? '—' }}</td>
            <td class="small">{{ $r['contract']?->end_date?->format('d/m/Y') ?? '— belum ada kontrak probation' }}</td>
            <td>
              @if($days === null) <span class="text-muted">—</span>
              @elseif($days < 0) <span class="badge badge-danger">Lewat {{ abs($days) }} hari</span>
              @else <span class="badge badge-{{ $days <= 14 ? 'warning' : 'light border' }}">{{ $days }} hari</span>
              @endif
            </td>
            <td class="small">
              @if($r['lastReview'])
                <span class="badge badge-{{ \App\Models\HR\ProbationReview::$decisionBadges[$r['lastReview']->decision] }}">{{ \App\Models\HR\ProbationReview::$decisionLabels[$r['lastReview']->decision] }}</span>
                {{ $r['lastReview']->review_date->format('d/m/Y') }}
              @else <span class="text-muted">Belum pernah</span> @endif
            </td>
            <td class="text-right"><a href="{{ route('hr.probation.show', $e) }}" class="btn btn-xs btn-outline-primary">Review</a></td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-muted small p-3">Tidak ada karyawan berstatus probation.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
