@extends('layouts.grain')
@section('title', 'Rekening Perusahaan')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Master Data</li>
        <li class="breadcrumb-item active">Rekening Perusahaan</li>
      </ol>
    </nav>

    <div class="h3 mb-2">Rekening Perusahaan</div>
    <p class="text-muted small mb-4">Rekening bank sumber pembayaran gaji untuk masing-masing perusahaan.</p>

    <form method="POST" action="{{ route('master.company-banks.store') }}" class="mb-4">
      @csrf
      <div class="form-row align-items-end">
        <div class="form-group col-md-2 mb-2">
          <label class="small text-muted mb-1">Perusahaan <span class="text-danger">*</span></label>
          <select name="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
            <option value="">— pilih —</option>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2 mb-2">
          <label class="small text-muted mb-1">Bank <span class="text-danger">*</span></label>
          <select name="bank_id" class="form-control @error('bank_id') is-invalid @enderror" required>
            <option value="">— pilih —</option>
            @foreach($banks as $b)<option value="{{ $b->id }}" @selected(old('bank_id') == $b->id)>{{ $b->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2 mb-2">
          <label class="small text-muted mb-1">No. Rekening <span class="text-danger">*</span></label>
          <input type="text" name="account_number" class="form-control" required value="{{ old('account_number') }}">
        </div>
        <div class="form-group col-md-2 mb-2">
          <label class="small text-muted mb-1">Atas Nama <span class="text-danger">*</span></label>
          <input type="text" name="account_name" class="form-control" required value="{{ old('account_name') }}">
        </div>
        <div class="form-group col-md-2 mb-2">
          <label class="small text-muted mb-1">Cabang</label>
          <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name') }}">
        </div>
        <div class="form-group col-md-1 mb-2">
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="new-primary" name="is_primary" value="1">
            <label class="custom-control-label small" for="new-primary">Utama</label>
          </div>
        </div>
        <div class="form-group col-auto mb-2">
          <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
      </div>
    </form>

    @if($companyBanks->isEmpty())
      <p class="text-muted small">Belum ada rekening perusahaan.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th>Perusahaan</th><th>Bank</th><th>No. Rekening</th><th>Atas Nama</th><th>Cabang</th>
              <th class="text-center" style="width:70px">Utama</th>
              <th class="text-center" style="width:70px">Aktif</th>
              <th style="width:80px"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($companyBanks as $cb)
            @php $f = 'cb-' . $cb->id; @endphp
            <tr>
              <td class="align-middle">{{ $cb->company?->short_name ?? $cb->company?->name ?? '-' }}</td>
              <td class="align-middle">
                <select name="bank_id" form="{{ $f }}" class="form-control form-control-sm">
                  @foreach($banks as $b)<option value="{{ $b->id }}" @selected($cb->bank_id == $b->id)>{{ $b->name }}</option>@endforeach
                </select>
                <input type="hidden" name="company_id" form="{{ $f }}" value="{{ $cb->company_id }}">
              </td>
              <td><input type="text" name="account_number" form="{{ $f }}" value="{{ $cb->account_number }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="account_name" form="{{ $f }}" value="{{ $cb->account_name }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="branch_name" form="{{ $f }}" value="{{ $cb->branch_name }}" class="form-control form-control-sm"></td>
              <td class="text-center align-middle">
                <input type="hidden" name="is_primary" form="{{ $f }}" value="0">
                <div class="custom-control custom-switch d-inline-block">
                  <input type="checkbox" class="custom-control-input" id="pri-{{ $cb->id }}" form="{{ $f }}" name="is_primary" value="1" {{ $cb->is_primary ? 'checked' : '' }}>
                  <label class="custom-control-label" for="pri-{{ $cb->id }}"></label>
                </div>
              </td>
              <td class="text-center align-middle">
                <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                <div class="custom-control custom-switch d-inline-block">
                  <input type="checkbox" class="custom-control-input" id="act-{{ $cb->id }}" form="{{ $f }}" name="is_active" value="1" {{ $cb->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label" for="act-{{ $cb->id }}"></label>
                </div>
              </td>
              <td class="align-middle text-nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger"
                   data-confirm="Hapus rekening {{ $cb->bank?->name }} — {{ $cb->account_number }}?"
                   data-confirm-title="Hapus Rekening"
                   data-form="del-cb-{{ $cb->id }}"><i class="gd-trash"></i></a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($companyBanks as $cb)
        <form id="cb-{{ $cb->id }}" method="POST" action="{{ route('master.company-banks.update', $cb) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="del-cb-{{ $cb->id }}" method="POST" action="{{ route('master.company-banks.destroy', $cb) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
