@extends('layouts.grain')
@section('title', 'Wilayah')

@section('content')
@include('components.notification')

@php
  $baseParams = array_filter([
    'province' => $selectedProvince?->id,
    'city'     => $selectedCity?->id,
    'district' => $selectedDistrict?->id,
  ]);
@endphp

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Master Data</li>
        <li class="breadcrumb-item active">Wilayah</li>
      </ol>
    </nav>

    <div class="h3 mb-2">Wilayah</div>
    <p class="text-muted small mb-4">
      Provinsi &rarr; Kota/Kabupaten &rarr; Kecamatan &rarr; Kelurahan/Desa. Provinsi &amp; kota utama sudah terisi
      (<code>RegionSeeder</code>); kecamatan/kelurahan diisi bertahap (dataset penuh Indonesia di-import terpisah dari wilayah.id).
    </p>

    {{-- ── Filter Provinsi + cari kota ── --}}
    <form method="GET" class="form-row align-items-end mb-3">
      <div class="form-group col-md-3 mb-2">
        <label class="small text-muted mb-1">Provinsi</label>
        <select name="province" class="form-control" onchange="this.form.submit()">
          @foreach($provinces as $p)
            <option value="{{ $p->id }}" @selected($selectedProvince && $selectedProvince->id == $p->id)>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small text-muted mb-1">Cari kota/kabupaten</label>
        <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="nama…">
      </div>
      <div class="form-group col-auto mb-2"><button type="submit" class="btn btn-outline-secondary">Filter</button></div>
    </form>

    <div class="row">
      {{-- ── Kolom Kota/Kabupaten ── --}}
      <div class="col-lg-6 mb-3">
        <h6 class="font-weight-bold">Kota / Kabupaten @if($selectedProvince)<span class="text-muted">di {{ $selectedProvince->name }}</span>@endif</h6>

        @if($selectedProvince)
        <form method="POST" action="{{ route('master.regions.store') }}" class="form-row align-items-end mb-2">
          @csrf
          <input type="hidden" name="province" value="{{ $selectedProvince->id }}">
          <input type="hidden" name="province_id" value="{{ $selectedProvince->id }}">
          <div class="form-group col-6 mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nama kota/kabupaten" required></div>
          <div class="form-group col-4 mb-2">
            <select name="type" class="form-control form-control-sm"><option value="kabupaten">Kabupaten</option><option value="kota">Kota</option></select>
          </div>
          <div class="form-group col-2 mb-2"><button type="submit" class="btn btn-sm btn-primary btn-block">+</button></div>
        </form>

        <div class="table-responsive" style="max-height:340px;overflow-y:auto">
          <table class="table table-sm mb-0">
            <thead class="thead-light"><tr><th>Nama</th><th style="width:90px">Tipe</th><th class="text-center" style="width:50px">On</th><th style="width:100px"></th></tr></thead>
            <tbody>
            @forelse($cities as $city)
              @php $f = 'city-' . $city->id; @endphp
              <tr class="{{ $selectedCity && $selectedCity->id == $city->id ? 'table-primary' : '' }}">
                <td><input type="text" name="name" form="{{ $f }}" value="{{ $city->name }}" class="form-control form-control-sm"></td>
                <td>
                  <select name="type" form="{{ $f }}" class="form-control form-control-sm">
                    <option value="kabupaten" @selected($city->type === 'kabupaten')>Kab.</option>
                    <option value="kota" @selected($city->type === 'kota')>Kota</option>
                  </select>
                </td>
                <td class="text-center align-middle">
                  <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                  <input type="checkbox" form="{{ $f }}" name="is_active" value="1" {{ $city->is_active ? 'checked' : '' }}>
                </td>
                <td class="align-middle text-nowrap">
                  <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                  <a href="{{ route('master.regions.index', ['province' => $selectedProvince->id, 'city' => $city->id]) }}" class="btn btn-xs btn-outline-primary">Kec.</a>
                  <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus {{ $city->name }}?" data-confirm-title="Hapus" data-form="del-city-{{ $city->id }}"><i class="gd-trash"></i></a>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-muted small py-3">Tidak ada.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
        @foreach($cities as $city)
          <form id="city-{{ $city->id }}" method="POST" action="{{ route('master.regions.update', $city) }}" class="d-none">@csrf @method('PUT')<input type="hidden" name="province" value="{{ $selectedProvince->id }}"></form>
          <form id="del-city-{{ $city->id }}" method="POST" action="{{ route('master.regions.destroy', $city) }}" class="d-none">@csrf @method('DELETE')<input type="hidden" name="province" value="{{ $selectedProvince->id }}"></form>
        @endforeach
        @endif
      </div>

      {{-- ── Kolom Kecamatan / Kelurahan ── --}}
      <div class="col-lg-6 mb-3">
        @if($selectedCity)
          <h6 class="font-weight-bold">Kecamatan <span class="text-muted">di {{ $selectedCity->name }}</span></h6>
          <form method="POST" action="{{ route('master.regions.districts.store') }}" class="form-row align-items-end mb-2">
            @csrf
            @foreach($baseParams as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <input type="hidden" name="city_id" value="{{ $selectedCity->id }}">
            <div class="form-group col-9 mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nama kecamatan" required></div>
            <div class="form-group col-3 mb-2"><button type="submit" class="btn btn-sm btn-primary btn-block">+</button></div>
          </form>

          <div class="table-responsive" style="max-height:200px;overflow-y:auto">
            <table class="table table-sm mb-0">
              <thead class="thead-light"><tr><th>Nama</th><th class="text-center" style="width:50px">On</th><th style="width:110px"></th></tr></thead>
              <tbody>
              @forelse($districts as $d)
                @php $f = 'dist-' . $d->id; @endphp
                <tr class="{{ $selectedDistrict && $selectedDistrict->id == $d->id ? 'table-primary' : '' }}">
                  <td><input type="text" name="name" form="{{ $f }}" value="{{ $d->name }}" class="form-control form-control-sm"></td>
                  <td class="text-center align-middle">
                    <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                    <input type="checkbox" form="{{ $f }}" name="is_active" value="1" {{ $d->is_active ? 'checked' : '' }}>
                  </td>
                  <td class="align-middle text-nowrap">
                    <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                    <a href="{{ route('master.regions.index', ['province' => $selectedProvince->id, 'city' => $selectedCity->id, 'district' => $d->id]) }}" class="btn btn-xs btn-outline-primary">Kel.</a>
                    <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus {{ $d->name }}?" data-confirm-title="Hapus" data-form="del-dist-{{ $d->id }}"><i class="gd-trash"></i></a>
                  </td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted small py-3">Belum ada kecamatan.</td></tr>
              @endforelse
              </tbody>
            </table>
          </div>
          @foreach($districts as $d)
            <form id="dist-{{ $d->id }}" method="POST" action="{{ route('master.regions.districts.update', $d) }}" class="d-none">@csrf @method('PUT')@foreach($baseParams as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach</form>
            <form id="del-dist-{{ $d->id }}" method="POST" action="{{ route('master.regions.districts.destroy', $d) }}" class="d-none">@csrf @method('DELETE')@foreach($baseParams as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach</form>
          @endforeach
        @else
          <p class="text-muted small">Pilih kota/kabupaten (tombol <strong>Kec.</strong>) untuk mengelola kecamatan.</p>
        @endif

        @if($selectedDistrict)
          <hr>
          <h6 class="font-weight-bold">Kelurahan / Desa <span class="text-muted">di {{ $selectedDistrict->name }}</span></h6>
          <form method="POST" action="{{ route('master.regions.villages.store') }}" class="form-row align-items-end mb-2">
            @csrf
            @foreach($baseParams as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <input type="hidden" name="district_id" value="{{ $selectedDistrict->id }}">
            <div class="form-group col-7 mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nama kelurahan/desa" required></div>
            <div class="form-group col-3 mb-2"><select name="type" class="form-control form-control-sm"><option value="kelurahan">Kelurahan</option><option value="desa">Desa</option></select></div>
            <div class="form-group col-2 mb-2"><button type="submit" class="btn btn-sm btn-primary btn-block">+</button></div>
          </form>

          <div class="table-responsive" style="max-height:200px;overflow-y:auto">
            <table class="table table-sm mb-0">
              <thead class="thead-light"><tr><th>Nama</th><th style="width:90px">Tipe</th><th class="text-center" style="width:50px">On</th><th style="width:70px"></th></tr></thead>
              <tbody>
              @forelse($villages as $v)
                @php $f = 'vil-' . $v->id; @endphp
                <tr>
                  <td><input type="text" name="name" form="{{ $f }}" value="{{ $v->name }}" class="form-control form-control-sm"></td>
                  <td>
                    <select name="type" form="{{ $f }}" class="form-control form-control-sm">
                      <option value="kelurahan" @selected($v->type === 'kelurahan')>Kel.</option>
                      <option value="desa" @selected($v->type === 'desa')>Desa</option>
                    </select>
                  </td>
                  <td class="text-center align-middle">
                    <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                    <input type="checkbox" form="{{ $f }}" name="is_active" value="1" {{ $v->is_active ? 'checked' : '' }}>
                  </td>
                  <td class="align-middle text-nowrap">
                    <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                    <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus {{ $v->name }}?" data-confirm-title="Hapus" data-form="del-vil-{{ $v->id }}"><i class="gd-trash"></i></a>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-muted small py-3">Belum ada kelurahan/desa.</td></tr>
              @endforelse
              </tbody>
            </table>
          </div>
          @foreach($villages as $v)
            <form id="vil-{{ $v->id }}" method="POST" action="{{ route('master.regions.villages.update', $v) }}" class="d-none">@csrf @method('PUT')@foreach($baseParams as $k => $val)<input type="hidden" name="{{ $k }}" value="{{ $val }}">@endforeach</form>
            <form id="del-vil-{{ $v->id }}" method="POST" action="{{ route('master.regions.villages.destroy', $v) }}" class="d-none">@csrf @method('DELETE')@foreach($baseParams as $k => $val)<input type="hidden" name="{{ $k }}" value="{{ $val }}">@endforeach</form>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
