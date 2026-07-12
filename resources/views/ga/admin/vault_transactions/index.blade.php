@extends('layouts.grain')
@section('title', 'Riwayat Transaksi Brankas')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Riwayat Transaksi Brankas</div>
</div>

{{-- Filter --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Dokumen</label>
        <select name="document_id" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($documents as $d)
            <option value="{{ $d->id }}" {{ request('document_id') == $d->id ? 'selected' : '' }}>{{ $d->barcode }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Status</label>
        <select name="status" class="form-control form-control-sm">
          <option value="">Semua</option>
          <option value="pengambilan" {{ request('status') == 'pengambilan' ? 'selected' : '' }}>Pengambilan</option>
          <option value="pengembalian" {{ request('status') == 'pengembalian' ? 'selected' : '' }}>Pengembalian</option>
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Keperluan</label>
        <select name="keperluan" class="form-control form-control-sm">
          <option value="">Semua</option>
          <option value="pinjam" {{ request('keperluan') == 'pinjam' ? 'selected' : '' }}>Pinjam</option>
          <option value="jual" {{ request('keperluan') == 'jual' ? 'selected' : '' }}>Jual</option>
          <option value="pengembalian_jaminan" {{ request('keperluan') == 'pengembalian_jaminan' ? 'selected' : '' }}>Pengembalian Jaminan</option>
          <option value="pengambilan_dokumen" {{ request('keperluan') == 'pengambilan_dokumen' ? 'selected' : '' }}>Pengambilan Dokumen</option>
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Tanggal</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('ga.admin.vault-transactions.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
    <table id="dt-vault-transactions" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>Tanggal</th>
          <th>Barcode</th>
          <th>Kategori</th>
          <th class="text-center">Status</th>
          <th>Nama</th>
          <th>Keperluan</th>
          <th>Dicatat Oleh</th>
          <th class="text-center">Foto</th>
        </tr>
      </thead>
      <tbody>
      @foreach($transactions as $t)
        <tr>
          <td><small>{{ $t->transaction_date->format('d/m/Y') }}</small></td>
          <td>
            <a href="{{ route('ga.admin.vault-documents.show', $t->document) }}">
              <span class="badge badge-dark">{{ $t->document->barcode }}</span>
            </a>
          </td>
          <td><small>{{ $t->document->category->name }}</small></td>
          <td class="text-center">
            @if($t->status === 'pengambilan')
              <span class="badge badge-warning">Pengambilan</span>
            @else
              <span class="badge badge-success">Pengembalian</span>
            @endif
          </td>
          <td>{{ $t->nama }}</td>
          <td><small>{{ ucwords(str_replace('_', ' ', $t->keperluan)) }}</small></td>
          <td><small>{{ $t->creator->name ?? '-' }}</small></td>
          <td class="text-center">
            <a href="{{ route('ga.admin.vault-transactions.photo', $t) }}" target="_blank" class="btn btn-xs btn-outline-info">
              <i class="gd-eye icon-text"></i>
            </a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$('#dt-vault-transactions').DataTable({ language: window.siproDtLang, order: [[0,'desc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
