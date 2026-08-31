@extends('layouts.grain')
@section('title', 'HR Analytics')

@section('content')
@include('components.notification')

<div class="h3 mb-0">Laporan & Rekap</div>

<ul class="nav nav-pills my-4">
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.index') }}">Rekap Umum</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.attendance-leave') }}">Absensi & Cuti</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.payroll') }}">Payroll Summary</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.headcount') }}">Headcount</a></li>
  <li class="nav-item"><a class="nav-link active" href="{{ route('laporan.analytics') }}">HR Analytics</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.report-builder') }}">Report Builder</a></li>
</ul>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">Semua — Konsolidasi Grup</option>
          @foreach($companies as $c)<option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Tahun</label>
        <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" onchange="this.form.submit()">
      </div>
      <div class="form-group col-auto mb-0">
        <a href="{{ route('laporan.analytics.pdf', ['company_id' => $companyId, 'year' => $year]) }}" class="btn btn-outline-danger btn-sm">
          <i class="gd-file mr-1"></i> Download PDF
        </a>
      </div>
    </form>
    <div class="small text-muted mt-1">Rasio turnover pakai headcount aktif saat ini sebagai penyebut (aproksimasi — data headcount historis per bulan tidak disimpan).</div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-6 col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #ef4444"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Turnover Rate {{ $year }}</div>
      <div class="h3 mb-0 font-weight-bold">{{ $data['turnover']['rate'] !== null ? $data['turnover']['rate'] . '%' : '—' }}</div>
      <div class="small text-muted">{{ $data['turnover']['total'] }} keluar / {{ $data['turnover']['avg_headcount'] }} headcount</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #f59e0b"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Absenteeism Rate {{ $year }}</div>
      <div class="h3 mb-0 font-weight-bold">{{ $data['absenteeism']['rate'] !== null ? $data['absenteeism']['rate'] . '%' : '—' }}</div>
      <div class="small text-muted">{{ $data['absenteeism']['alpha_days'] }} alpha / {{ $data['absenteeism']['total_records'] }} record absensi</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #2563eb"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Cost per Hire {{ $year }}</div>
      <div class="h3 mb-0 font-weight-bold">{{ $data['cost_per_hire']['cost_per_hire'] !== null ? 'Rp' . number_format($data['cost_per_hire']['cost_per_hire'], 0, ',', '.') : '—' }}</div>
      <div class="small text-muted">{{ $data['cost_per_hire']['hires'] }} hire, total biaya Rp {{ number_format($data['cost_per_hire']['total_cost'], 0, ',', '.') }}
        <a href="{{ route('recruitment.costs.index', ['company_id' => $companyId]) }}">catat biaya</a></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #22c55e"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Efektivitas Training {{ $year }}</div>
      <div class="h3 mb-0 font-weight-bold">{{ $data['training']['rate'] !== null ? $data['training']['rate'] . '%' : '—' }}</div>
      <div class="small text-muted">{{ $data['training']['completed'] }}/{{ $data['training']['total'] }} selesai
        @if($data['training']['avg_score'] !== null) &bull; rata skor {{ $data['training']['avg_score'] }} @endif
      </div>
    </div></div>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header font-weight-bold">Turnover — Voluntary vs Involuntary</div>
      <div class="card-body"><canvas id="chart-turnover-type" height="180"></canvas></div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header font-weight-bold">Turnover per Departemen</div>
      <div class="card-body">
        @if($data['turnover']['by_department']->isNotEmpty())<canvas id="chart-turnover-dept" height="180"></canvas>@else<p class="text-muted small mb-0">Tidak ada data.</p>@endif
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header font-weight-bold">Absenteeism Rate Bulanan</div>
      <div class="card-body"><canvas id="chart-absen-monthly" height="180"></canvas></div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header font-weight-bold">Absenteeism per Departemen</div>
      <div class="card-body">
        @if($data['absenteeism']['by_department']->isNotEmpty())<canvas id="chart-absen-dept" height="180"></canvas>@else<p class="text-muted small mb-0">Tidak ada data.</p>@endif
      </div>
    </div>
  </div>
  <div class="col-md-12 mb-4">
    <div class="card">
      <div class="card-header font-weight-bold">Efektivitas Training per Program</div>
      <div class="card-body p-0">
        @if($data['training']['by_program']->isEmpty())
          <p class="text-muted small mb-0 p-3">Tidak ada data training tahun ini.</p>
        @else
          <table class="table table-sm mb-0">
            <thead class="thead-light"><tr><th>Program</th><th class="text-center">Peserta</th><th class="text-center">Selesai</th><th class="text-center">Completion Rate</th></tr></thead>
            <tbody>
            @foreach($data['training']['by_program'] as $prog => $row)
              <tr>
                <td>{{ $prog }}</td>
                <td class="text-center">{{ $row['total'] }}</td>
                <td class="text-center">{{ $row['completed'] }}</td>
                <td class="text-center">{{ $row['total'] > 0 ? round($row['completed'] / $row['total'] * 100) . '%' : '—' }}</td>
              </tr>
            @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
  var palette = ['#0f2a4a','#1a3f6f','#2563eb','#0ea5e9','#22c55e','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'];
  var monthNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

  function doughnut(id, map) {
    var el = document.getElementById(id);
    if (!el) return;
    var labels = Object.keys(map), data = Object.values(map);
    if (!data.length) return;
    new Chart(el, {
      type: 'doughnut',
      data: { labels: labels, datasets: [{ data: data, backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }] },
      options: { plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } }, cutout: '58%', maintainAspectRatio: false }
    });
  }

  function hbar(id, map, label) {
    var el = document.getElementById(id);
    if (!el) return;
    var labels = Object.keys(map), data = Object.values(map);
    if (!data.length) return;
    new Chart(el, {
      type: 'bar',
      data: { labels: labels, datasets: [{ label: label || '', data: data, backgroundColor: '#1a3f6f', borderRadius: 5 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } }, maintainAspectRatio: false }
    });
  }

  function lineByMonth(id, map, label) {
    var el = document.getElementById(id);
    if (!el) return;
    var labels = [], data = [];
    for (var m = 1; m <= 12; m++) { labels.push(monthNames[m]); data.push(map[m] || 0); }
    new Chart(el, {
      type: 'line',
      data: { labels: labels, datasets: [{ label: label || '', data: data, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.15)', fill: true, tension: .3 }] },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }, maintainAspectRatio: false }
    });
  }

  doughnut('chart-turnover-type', { 'Voluntary': {{ $data['turnover']['voluntary'] }}, 'Involuntary': {{ $data['turnover']['involuntary'] }} });
  hbar('chart-turnover-dept', @json($data['turnover']['by_department']), 'Keluar');
  lineByMonth('chart-absen-monthly', @json($data['absenteeism']['monthly']), 'Absenteeism %');
  hbar('chart-absen-dept', @json($data['absenteeism']['by_department']), 'Absenteeism %');
})();
</script>
@endsection
