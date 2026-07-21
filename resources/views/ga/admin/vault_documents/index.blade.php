@extends('layouts.grain')
@section('title', 'Dokumen Brankas')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
  <div class="h3 mb-0">Dokumen Brankas</div>
  <div>
    <a href="{{ route('ga.admin.vaults.index') }}" class="btn btn-outline-secondary mr-1">
      <i class="gd-lock mr-1"></i> Kelola Berangkas
    </a>
    <a href="{{ route('ga.admin.vault-categories.index') }}" class="btn btn-outline-secondary mr-1">
      <i class="gd-tag mr-1"></i> Kelola Kategori
    </a>
    <a href="{{ route('ga.admin.vault-documents.create') }}" class="btn btn-primary">
      <i class="gd-plus mr-1"></i> Tambah Dokumen
    </a>
  </div>
</div>

{{-- Filter --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Berangkas</label>
        <select name="vault_id" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($vaults as $v)
            <option value="{{ $v->id }}" {{ request('vault_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Kategori</label>
        <select name="category_id" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">Cari</label>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Barcode / detail dokumen" value="{{ request('q') }}">
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('ga.admin.vault-documents.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
    <table id="dt-vault-documents" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>Barcode</th>
          <th>Berangkas</th>
          <th>Kategori</th>
          <th>Detail Dokumen</th>
          <th class="text-center">Status</th>
          <th class="text-center">Transaksi</th>
          <th class="text-center">Aktif</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($documents as $d)
        <tr>
          <td><span class="badge badge-dark" style="font-size:.85rem;letter-spacing:.06em">{{ $d->barcode }}</span></td>
          <td>
            @if($d->vault)
              {{ $d->vault->name }}
            @else
              <span class="badge badge-danger">Belum diset</span>
            @endif
          </td>
          <td>{{ $d->category->name }}</td>
          <td style="max-width:280px"><small>{{ \Illuminate\Support\Str::limit($d->detail, 80) }}</small></td>
          <td class="text-center">
            @if($d->isOut())
              <span class="badge badge-warning">Diambil</span>
            @else
              <span class="badge badge-success">Di Brankas</span>
            @endif
          </td>
          <td class="text-center">{{ $d->transactions_count }}</td>
          <td class="text-center">
            @if($d->is_active) <span class="badge badge-success">Ya</span>
            @else <span class="badge badge-secondary">Non-aktif</span> @endif
          </td>
          <td class="text-right" style="white-space:nowrap">
            <a href="{{ route('ga.admin.vault-documents.show', $d) }}" class="btn btn-xs btn-outline-info mr-1" title="Detail & Transaksi">
              <i class="gd-eye icon-text"></i>
            </a>
            <a href="{{ route('ga.admin.vault-documents.edit', $d) }}" class="btn btn-xs btn-outline-warning mr-1">
              <i class="gd-pencil icon-text"></i>
            </a>
            <form method="POST" action="{{ route('ga.admin.vault-documents.destroy', $d) }}" class="d-inline">
              @csrf @method('DELETE')
              <button type="button" class="btn btn-xs btn-outline-danger"
                      data-confirm="Hapus dokumen {{ $d->barcode }}?"
                      data-confirm-title="Hapus Dokumen"
                      data-confirm-type="danger"
                      data-form="this.closest('form')">
                <i class="gd-trash icon-text"></i>
              </button>
            </form>
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
$('#dt-vault-documents').DataTable({ language: window.siproDtLang, order: [[0,'desc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
