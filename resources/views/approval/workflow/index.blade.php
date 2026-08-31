@extends('layouts.grain')
@section('title', 'Pengaturan Approval')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Pengaturan Approval</li>
      </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem">
      <div class="h3 mb-2">Pengaturan Alur Persetujuan</div>
      <a href="{{ route('approval.workflows.log') }}" class="btn btn-outline-secondary btn-sm">
        <i class="gd-time mr-1"></i> Riwayat Perubahan
      </a>
    </div>
    <p class="text-muted small mb-4">Atur siapa saja yang menyetujui tiap jenis transaksi, per perusahaan. Perubahan berlaku untuk pengajuan baru.</p>

    <form method="GET" class="form-row align-items-end mb-4">
      <div class="form-group col-md-3 mb-2">
        <label class="small text-muted mb-1">Perusahaan</label>
        <select name="company" class="form-control" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected($company && $company->id == $c->id)>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
    </form>

    @if($company)
      <div class="table-responsive">
        <table class="table table-sm">
          <thead class="thead-light"><tr><th>Jenis Transaksi</th><th class="text-center" style="width:120px">Jumlah Step</th><th style="width:120px"></th></tr></thead>
          <tbody>
          @foreach($types as $key => $label)
            @php $wf = $workflows->get($key); @endphp
            <tr>
              <td class="align-middle">{{ $label }}
                @if(!$wf || !$wf->is_active)<span class="badge badge-light ml-1">belum diatur</span>@endif
              </td>
              <td class="text-center align-middle">{{ $wf->steps_count ?? 0 }}</td>
              <td class="align-middle">
                <a href="{{ route('approval.workflows.edit', [$company, $key]) }}" class="btn btn-sm btn-outline-primary">Atur Alur</a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      <hr class="my-4">
      <h6 class="font-weight-bold mb-2">Salin Alur ke Perusahaan Lain</h6>
      <form method="POST" action="{{ route('approval.workflows.copy') }}" class="form-row align-items-end">
        @csrf
        <div class="form-group col-md-3 mb-2">
          <label class="small text-muted mb-1">Dari</label>
          <select name="from_company_id" class="form-control">
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected($company->id == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-3 mb-2">
          <label class="small text-muted mb-1">Ke</label>
          <select name="to_company_id" class="form-control">
            @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-auto mb-2">
          <button class="btn btn-outline-secondary" onclick="return confirm('Salin semua workflow? Alur di perusahaan tujuan akan ditimpa.')">Salin</button>
        </div>
      </form>
    @endif
  </div>
</div>
@endsection
