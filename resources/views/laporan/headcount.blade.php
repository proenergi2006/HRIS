@extends('layouts.grain')
@section('title', 'Dashboard Headcount')

@section('content')
@include('components.notification')

<div class="h3 mb-0">Laporan & Rekap</div>

<ul class="nav nav-pills my-4">
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.index') }}">Rekap Umum</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.attendance-leave') }}">Absensi & Cuti</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.payroll') }}">Payroll Summary</a></li>
  <li class="nav-item"><a class="nav-link active" href="{{ route('laporan.headcount') }}">Headcount</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.analytics') }}">HR Analytics</a></li>
</ul>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">Semua — Konsolidasi Grup</option>
          @foreach($companies as $c)<option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <a href="{{ route('laporan.headcount.pdf', ['company_id' => $companyId]) }}" class="btn btn-outline-danger btn-sm">
          <i class="gd-file mr-1"></i> Download PDF
        </a>
      </div>
    </form>
    <div class="small text-muted mt-1">Headcount = karyawan aktif per hari ini.</div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-6 col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #0F2A4A"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">{{ $companyId ? 'Headcount PT ini' : 'Headcount Grup' }}</div>
      <div class="h3 mb-0 font-weight-bold">{{ $data['total'] }}</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #2563eb"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Total Grup (semua PT)</div>
      <div class="h3 mb-0 font-weight-bold">{{ $data['group_total'] }}</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #16a34a"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Karyawan Baru Bln Ini</div>
      <div class="h3 mb-0 font-weight-bold">{{ $data['new_this_month'] }}</div>
      <div class="small text-muted">{{ $data['new_this_year'] }} tahun ini</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #d97706"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Kontrak Segera Habis</div>
      <div class="h3 mb-0 font-weight-bold">{{ $data['contract_ending'] }}</div>
      <div class="small text-muted">&le; 2 bulan lagi</div>
    </div></div>
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-4">
    <div class="card h-100"><div class="card-header font-weight-bold">Per Status Kepegawaian</div>
      <div class="card-body"><canvas id="chart-status" height="200"></canvas>
        <table class="table table-sm mt-3 mb-0">
          @foreach($data['by_status'] as $k => $v)<tr><td>{{ $k }}</td><td class="text-right font-weight-bold">{{ $v }}</td></tr>@endforeach
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card h-100"><div class="card-header font-weight-bold">Per Tipe Karyawan</div>
      <div class="card-body"><canvas id="chart-type" height="200"></canvas>
        <table class="table table-sm mt-3 mb-0">
          @foreach($data['by_type'] as $k => $v)<tr><td>{{ $k }}</td><td class="text-right font-weight-bold">{{ $v }}</td></tr>@endforeach
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card h-100"><div class="card-header font-weight-bold">Per Jenis Kelamin</div>
      <div class="card-body"><canvas id="chart-gender" height="200"></canvas>
        <table class="table table-sm mt-3 mb-0">
          @foreach($data['by_gender'] as $k => $v)<tr><td>{{ $k }}</td><td class="text-right font-weight-bold">{{ $v }}</td></tr>@endforeach
        </table>
      </div>
    </div>
  </div>
</div>

@if($data['by_company']->isNotEmpty())
<div class="card mb-4">
  <div class="card-header font-weight-bold">Per Perusahaan (Konsolidasi Grup)</div>
  <div class="card-body">
    <canvas id="chart-company" height="90"></canvas>
    <table class="table table-sm mt-3 mb-0">
      <thead class="thead-light"><tr><th>Perusahaan</th><th class="text-right">Headcount</th></tr></thead>
      @foreach($data['by_company'] as $k => $v)<tr><td>{{ $k }}</td><td class="text-right font-weight-bold">{{ $v }}</td></tr>@endforeach
    </table>
  </div>
</div>
@endif

<div class="row">
  @foreach([
    ['Per Departemen', 'by_department', 'chart-dept'],
    ['Per Divisi', 'by_division', 'chart-div'],
    ['Per Level Jabatan', 'by_level', 'chart-level'],
    ['Per Section', 'by_section', null],
  ] as [$title, $key, $canvasId])
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header font-weight-bold">{{ $title }}</div>
      <div class="card-body">
        @if($canvasId && $data[$key]->isNotEmpty())<canvas id="{{ $canvasId }}" height="{{ max(80, $data[$key]->count() * 22) }}"></canvas>@endif
        <table class="table table-sm mb-0 {{ $canvasId ? 'mt-3' : '' }}">
          <thead class="thead-light"><tr><th>{{ str_replace('Per ', '', $title) }}</th><th class="text-right">Headcount</th></tr></thead>
          <tbody>
            @forelse($data[$key] as $k => $v)<tr><td>{{ $k }}</td><td class="text-right font-weight-bold">{{ $v }}</td></tr>@empty<tr><td colspan="2" class="text-muted text-center py-3">Tidak ada data.</td></tr>@endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endsection

@section('scripts')
<script>
(function () {
  var palette = ['#0f2a4a','#1a3f6f','#2563eb','#0ea5e9','#22c55e','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'];

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

  function hbar(id, map) {
    var el = document.getElementById(id);
    if (!el) return;
    var labels = Object.keys(map), data = Object.values(map);
    if (!data.length) return;
    new Chart(el, {
      type: 'bar',
      data: { labels: labels, datasets: [{ label: 'Headcount', data: data, backgroundColor: '#1a3f6f', borderRadius: 5 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } }, maintainAspectRatio: false }
    });
  }

  doughnut('chart-status', @json($data['by_status']));
  doughnut('chart-type',   @json($data['by_type']));
  doughnut('chart-gender', @json($data['by_gender']));
  hbar('chart-company', @json($data['by_company']));
  hbar('chart-dept',  @json($data['by_department']));
  hbar('chart-div',   @json($data['by_division']));
  hbar('chart-level', @json($data['by_level']));
})();
</script>
@endsection
