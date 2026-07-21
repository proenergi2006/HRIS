@extends('layouts.grain')
@section('title', 'Berangkas Dokumen')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
  <div class="h3 mb-0">Berangkas Dokumen</div>
  <div>
    <a href="{{ route('ga.admin.vault-documents.index') }}" class="btn btn-outline-secondary mr-1">
      <i class="gd-file-text mr-1"></i> Daftar Dokumen
    </a>
    <a href="{{ route('ga.admin.vaults.create') }}" class="btn btn-primary">
      <i class="gd-plus mr-1"></i> Tambah Berangkas
    </a>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
    <table id="dt-vaults" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>Barcode</th>
          <th>Nama Berangkas</th>
          <th class="text-center">Jumlah Dokumen</th>
          <th class="text-center">Aktif</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($vaults as $v)
        <tr>
          <td><span class="badge badge-dark" style="font-size:.85rem;letter-spacing:.06em">{{ $v->barcode }}</span></td>
          <td>{{ $v->name }}</td>
          <td class="text-center">{{ $v->documents_count }}</td>
          <td class="text-center">
            @if($v->is_active) <span class="badge badge-success">Ya</span>
            @else <span class="badge badge-secondary">Non-aktif</span> @endif
          </td>
          <td class="text-right" style="white-space:nowrap">
            <a href="{{ route('ga.admin.vaults.show', $v) }}" class="btn btn-xs btn-outline-info mr-1" title="Detail Isi Berangkas">
              <i class="gd-eye icon-text"></i>
            </a>
            <a href="{{ route('ga.admin.vaults.qrcode', $v) }}" class="btn btn-xs btn-outline-primary mr-1" title="Barcode / QR">
              <i class="gd-layers icon-text"></i>
            </a>
            <a href="{{ route('ga.admin.vaults.edit', $v) }}" class="btn btn-xs btn-outline-warning mr-1">
              <i class="gd-pencil icon-text"></i>
            </a>
            <form method="POST" action="{{ route('ga.admin.vaults.destroy', $v) }}" class="d-inline">
              @csrf @method('DELETE')
              <button type="button" class="btn btn-xs btn-outline-danger"
                      data-confirm="Hapus berangkas {{ $v->name }}?"
                      data-confirm-title="Hapus Berangkas"
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
$('#dt-vaults').DataTable({ language: window.siproDtLang, order: [[1,'asc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
