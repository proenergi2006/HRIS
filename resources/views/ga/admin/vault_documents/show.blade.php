@extends('layouts.grain')
@section('title', 'Dokumen Brankas — ' . $document->barcode)

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('ga.admin.vault-documents.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali
  </a>
</div>

<div class="row">
  {{-- Detail dokumen --}}
  <div class="col-12 col-md-5 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <h6 class="font-weight-bold mb-0">Informasi Dokumen</h6>
          @if($document->isOut())
            <span class="badge badge-warning">Diambil</span>
          @else
            <span class="badge badge-success">Di Brankas</span>
          @endif
        </div>
        <div class="table-responsive">
        <table class="table table-sm mb-0">
          <tr><td class="text-muted" style="width:40%">Barcode</td><td><span class="badge badge-dark">{{ $document->barcode }}</span></td></tr>
          <tr><td class="text-muted">Kategori</td><td class="font-weight-bold">{{ $document->category->name }}</td></tr>
          <tr><td class="text-muted">Detail</td><td>{{ $document->detail }}</td></tr>
          <tr><td class="text-muted">Aktif</td><td>{{ $document->is_active ? 'Ya' : 'Non-aktif' }}</td></tr>
        </table>
        </div>
        <a href="{{ route('ga.admin.vault-documents.qrcode', $document) }}" class="btn btn-sm btn-outline-primary mt-3">
          <i class="gd-layers mr-1"></i> Lihat Barcode
        </a>
      </div>
    </div>
  </div>

  {{-- Tambah transaksi --}}
  <div class="col-12 col-md-7 mb-4">
    <div class="card">
      <div class="card-header font-weight-bold">Tambah Transaksi</div>
      <div class="card-body">
        <form method="POST" action="{{ route('ga.admin.vault-documents.transactions.store', $document) }}" enctype="multipart/form-data">
          @csrf

          <div class="form-group">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <div>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="status-pengambilan" name="status" value="pengambilan" class="custom-control-input"
                       {{ old('status') == 'pengambilan' ? 'checked' : '' }}>
                <label class="custom-control-label" for="status-pengambilan">Pengambilan</label>
              </div>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="status-pengembalian" name="status" value="pengembalian" class="custom-control-input"
                       {{ old('status') == 'pengembalian' ? 'checked' : '' }}>
                <label class="custom-control-label" for="status-pengembalian">Pengembalian</label>
              </div>
            </div>
            @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Tanggal <span class="text-danger">*</span></label>
              <input type="date" name="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror"
                     value="{{ old('transaction_date', date('Y-m-d')) }}">
              @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group col-md-6">
              <label>Nama <span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                     placeholder="Nama yang mengambil/mengembalikan" value="{{ old('nama') }}">
              @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="form-group">
            <label>Keperluan <span class="text-danger">*</span></label>
            <select name="keperluan" class="form-control @error('keperluan') is-invalid @enderror">
              <option value="">-- Pilih Keperluan --</option>
              <option value="pinjam" {{ old('keperluan') == 'pinjam' ? 'selected' : '' }}>Pinjam</option>
              <option value="jual" {{ old('keperluan') == 'jual' ? 'selected' : '' }}>Jual</option>
              <option value="pengembalian_jaminan" {{ old('keperluan') == 'pengembalian_jaminan' ? 'selected' : '' }}>Pengembalian Jaminan</option>
              <option value="pengambilan_dokumen" {{ old('keperluan') == 'pengambilan_dokumen' ? 'selected' : '' }}>Pengambilan Dokumen</option>
            </select>
            @error('keperluan') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group mb-0">
            <label>Foto Serah Terima <span class="text-danger">*</span></label>
            <div class="border rounded p-2 text-center" style="cursor:pointer" onclick="document.getElementById('photo_handover').click()" id="photo-box">
              <img id="photo-preview" style="display:none;max-height:180px;max-width:100%;border-radius:6px;margin-bottom:8px">
              <div id="photo-placeholder" class="text-muted small py-4">
                <i class="gd-image" style="font-size:22px;display:block;margin-bottom:6px"></i>
                Klik untuk ambil / pilih foto serah terima
              </div>
            </div>
            <input type="file" id="photo_handover" name="photo_handover" accept="image/*" capture="environment"
                   class="d-none @error('photo_handover') is-invalid @enderror" onchange="vaultPreviewPhoto(this)">
            @error('photo_handover') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <hr>
          <button type="submit" class="btn btn-primary btn-sm">Simpan Transaksi</button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Riwayat transaksi --}}
<div class="card">
  <div class="card-header font-weight-bold">Riwayat Transaksi</div>
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="thead-light">
        <tr>
          <th>Tanggal</th>
          <th>Status</th>
          <th>Nama</th>
          <th>Keperluan</th>
          <th>Dicatat Oleh</th>
          <th class="text-center">Foto</th>
        </tr>
      </thead>
      <tbody>
      @forelse($transactions as $t)
        <tr>
          <td><small>{{ $t->transaction_date->format('d/m/Y') }}</small></td>
          <td>
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
      @empty
        <tr><td colspan="6" class="text-center text-muted small py-3">Belum ada transaksi.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function vaultPreviewPhoto(input) {
  var file = input.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    var prev = document.getElementById('photo-preview');
    prev.src = e.target.result;
    prev.style.display = 'inline-block';
    document.getElementById('photo-placeholder').style.display = 'none';
  };
  reader.readAsDataURL(file);
}
</script>
@endsection
