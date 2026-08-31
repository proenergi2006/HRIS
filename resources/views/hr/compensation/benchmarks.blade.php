@extends('layouts.grain')
@section('title', 'Benchmark Gaji')

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <div class="h3 mb-0">Benchmark Gaji Pasar</div>
  <a href="{{ route('hr.compensation.comparison') }}" class="btn btn-sm btn-outline-primary">
    <i class="gd-bar-chart mr-1"></i>Lihat Perbandingan
  </a>
</div>

<div class="alert alert-info small mb-4">
  Benchmark ditetapkan per <strong>Level</strong> (berlaku lintas 3 PT — Level adalah golongan
  jabatan yang sama di seluruh grup). Isi kisaran gaji pasar (Min / Tengah / Maks) hasil survei
  gaji atau referensi eksternal, lalu bandingkan dengan gaji aktual karyawan di menu Perbandingan.
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>Level</th>
            <th style="width:160px">Min (Rp)</th>
            <th style="width:160px">Tengah (Rp)</th>
            <th style="width:160px">Maks (Rp)</th>
            <th style="width:180px">Sumber</th>
            <th>Catatan</th>
            <th style="width:70px"></th>
          </tr>
        </thead>
        <tbody>
        @foreach($levels as $level)
          @php $f = 'bench-form-' . $level->id; $b = $level->benchmark; @endphp
          <tr>
            <td class="align-middle font-weight-bold">{{ $level->name }}</td>
            <td><input type="number" data-rupiah name="market_min" form="{{ $f }}" value="{{ $b->market_min ?? '' }}" class="form-control form-control-sm" min="0" required></td>
            <td><input type="number" data-rupiah name="market_mid" form="{{ $f }}" value="{{ $b->market_mid ?? '' }}" class="form-control form-control-sm" min="0" required></td>
            <td><input type="number" data-rupiah name="market_max" form="{{ $f }}" value="{{ $b->market_max ?? '' }}" class="form-control form-control-sm" min="0" required></td>
            <td><input type="text" name="source" form="{{ $f }}" value="{{ $b->source ?? '' }}" class="form-control form-control-sm" placeholder="mis. Survei Mercer 2026"></td>
            <td><input type="text" name="notes" form="{{ $f }}" value="{{ $b->notes ?? '' }}" class="form-control form-control-sm"></td>
            <td class="text-right">
              <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-primary">Simpan</button>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @if($levels->isEmpty())
    <div class="card-body text-muted small">Belum ada data Level.</div>
  @endif
</div>

@foreach($levels as $level)
  <form id="bench-form-{{ $level->id }}" method="POST" action="{{ route('hr.compensation.benchmarks.update', $level) }}" class="d-none">@csrf @method('PUT')</form>
@endforeach

@endsection
