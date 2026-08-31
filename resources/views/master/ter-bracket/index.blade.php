@extends('layouts.grain')
@section('title', 'Tarif PPh21 (TER)')

@section('content')
@include('components.notification')

<div class="h3 mb-3">Tarif PPh21 — TER (Tarif Efektif Rata-rata)</div>

<div class="alert alert-warning">
  <strong>Perkiraan/ilustratif.</strong> Batas penghasilan &amp; persentase di bawah ini
  BELUM disalin persis dari tabel resmi Lampiran PMK 168/2023 — validasi paralel dengan
  jPayroll dulu sebelum dipakai untuk penggajian resmi, lalu sesuaikan angkanya di sini.
  Kategori TER (A/B/C) ditentukan otomatis dari status PTKP karyawan (status kawin + jumlah
  tanggungan anak, maks. 3).
</div>

@foreach($categories as $cat)
<div class="card mb-4">
  <div class="card-header font-weight-bold">
    Kategori {{ $cat->code }}
    <span class="text-muted font-weight-normal">— {{ $cat->description }}</span>
  </div>
  <div class="card-body">

    <form method="POST" action="{{ route('master.ter-brackets.store') }}" class="form-row align-items-end mb-3">
      @csrf
      <input type="hidden" name="ter_category_id" value="{{ $cat->id }}">
      <div class="form-group col-md-3 mb-2">
        <label class="small">Dari (Rp)</label>
        <input type="number" data-rupiah name="income_from" class="form-control form-control-sm" min="0" required>
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small">Sampai (Rp, kosongkan = tak terhingga)</label>
        <input type="number" data-rupiah name="income_to" class="form-control form-control-sm" min="0">
      </div>
      <div class="form-group col-md-2 mb-2">
        <label class="small">Tarif (%)</label>
        <input type="number" name="rate_percent" class="form-control form-control-sm" min="0" max="100" step="0.01" required>
      </div>
      <div class="form-group col-md-2 mb-2">
        <button type="submit" class="btn btn-sm btn-primary btn-block">Tambah</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr><th>Dari</th><th>Sampai</th><th>Tarif (%)</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($cat->brackets as $b)
          @php $f = 'ter-form-' . $b->id; @endphp
          <tr>
            <td style="width:200px">
              <input type="number" data-rupiah name="income_from" form="{{ $f }}" value="{{ $b->income_from }}" class="form-control form-control-sm">
            </td>
            <td style="width:200px">
              <input type="number" data-rupiah name="income_to" form="{{ $f }}" value="{{ $b->income_to }}" class="form-control form-control-sm" placeholder="tak terhingga">
            </td>
            <td style="width:120px">
              <input type="number" name="rate_percent" form="{{ $f }}" value="{{ $b->rate_percent }}" step="0.01" class="form-control form-control-sm">
            </td>
            <td style="width:90px;white-space:nowrap">
              <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
              <a href="#" class="btn btn-xs btn-outline-danger"
                 data-confirm="Hapus lapisan tarif ini?" data-confirm-title="Hapus Tarif"
                 data-form="ter-delete-{{ $b->id }}">
                <i class="gd-trash"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-muted small">Belum ada lapisan tarif.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    @foreach($cat->brackets as $b)
      <form id="ter-form-{{ $b->id }}" method="POST" action="{{ route('master.ter-brackets.update', $b) }}" class="d-none">@csrf @method('PUT')</form>
      <form id="ter-delete-{{ $b->id }}" method="POST" action="{{ route('master.ter-brackets.destroy', $b) }}" class="d-none">@csrf @method('DELETE')</form>
    @endforeach

  </div>
</div>
@endforeach
@endsection
