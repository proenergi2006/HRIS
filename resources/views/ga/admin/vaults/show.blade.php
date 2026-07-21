@extends('layouts.grain')
@section('title', 'Berangkas — ' . $vault->name)

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('ga.admin.vaults.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali
  </a>
</div>

<div class="row">
  <div class="col-12 col-md-5 mb-4">
    <div class="card">
      <div class="card-body">
        <h6 class="font-weight-bold mb-3">Informasi Berangkas</h6>
        <div class="table-responsive">
        <table class="table table-sm mb-0">
          <tr><td class="text-muted" style="width:40%">Barcode</td><td><span class="badge badge-dark">{{ $vault->barcode }}</span></td></tr>
          <tr><td class="text-muted">Nama</td><td class="font-weight-bold">{{ $vault->name }}</td></tr>
          <tr><td class="text-muted">Jumlah Dokumen</td><td>{{ $documents->count() }}</td></tr>
          <tr><td class="text-muted">Aktif</td><td>{{ $vault->is_active ? 'Ya' : 'Non-aktif' }}</td></tr>
        </table>
        </div>
        <a href="{{ route('ga.admin.vaults.qrcode', $vault) }}" class="btn btn-sm btn-outline-primary mt-3">
          <i class="gd-layers mr-1"></i> Lihat Barcode
        </a>
        <a href="{{ route('ga.admin.vault-documents.create') }}" class="btn btn-sm btn-outline-secondary mt-3">
          <i class="gd-plus mr-1"></i> Tambah Dokumen
        </a>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-7 mb-4">
    <div class="card">
      <div class="card-header font-weight-bold">Isi Berangkas</div>
      <div class="card-body">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>Barcode</th>
              <th>Detail Dokumen</th>
              <th class="text-center">Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          @forelse($documents as $d)
            <tr>
              <td><span class="badge badge-dark">{{ $d->barcode }}</span></td>
              <td style="max-width:220px"><small>{{ \Illuminate\Support\Str::limit($d->detail, 60) }}</small></td>
              <td class="text-center">
                @if($d->isOut())
                  <span class="badge badge-warning">Diambil</span>
                @else
                  <span class="badge badge-success">Di Brankas</span>
                @endif
              </td>
              <td class="text-right">
                <a href="{{ route('ga.admin.vault-documents.show', $d) }}" class="btn btn-xs btn-outline-info" title="Detail & Transaksi">
                  <i class="gd-eye icon-text"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted small py-3">Belum ada dokumen di berangkas ini.</td></tr>
          @endforelse
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
