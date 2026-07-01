@php
  $editing  = $editing ?? false;
  $p        = $perdin ?? null;
  $catLabels = \App\Models\Perdin\PerdinRequest::$categoryLabels;
@endphp

@if($errors->any())
<div class="alert alert-danger mb-3">
  <strong>Terdapat kesalahan, silakan periksa kembali:</strong>
  <ul class="mb-0 mt-1">
    @foreach($errors->all() as $err)
      <li>{{ $err }}</li>
    @endforeach
  </ul>
</div>
@endif

{{-- 1. Data Perjalanan --}}
<div class="card mb-3">
  <div class="card-header font-weight-bold">1. Data Perjalanan</div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-group col-md-6">
        <label class="font-weight-bold">Departemen</label>
        <input type="text" name="department" class="form-control @error('department') is-invalid @enderror"
               value="{{ old('department', $p?->department ?? auth()->user()->department) }}" placeholder="Departemen">
        @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group col-md-6">
        <label class="font-weight-bold">Tujuan / Kota <span class="text-danger">*</span></label>
        <input type="text" name="destination" class="form-control @error('destination') is-invalid @enderror"
               value="{{ old('destination', $p?->destination) }}" placeholder="Mis. Palu" required>
        @error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-3">
        <label class="font-weight-bold">Tgl Berangkat <span class="text-danger">*</span></label>
        <input type="date" name="departure_date" class="form-control @error('departure_date') is-invalid @enderror"
               value="{{ old('departure_date', $p?->departure_date?->format('Y-m-d')) }}" required>
        @error('departure_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group col-md-3">
        <label class="font-weight-bold">Jam Berangkat</label>
        <input type="time" name="departure_time" class="form-control"
               value="{{ old('departure_time', $p?->departure_time) }}">
      </div>
      <div class="form-group col-md-3">
        <label class="font-weight-bold">Tgl Kembali <span class="text-danger">*</span></label>
        <input type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror"
               value="{{ old('return_date', $p?->return_date?->format('Y-m-d')) }}" required>
        @error('return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group col-md-3">
        <label class="font-weight-bold">Jam Kembali</label>
        <input type="time" name="return_time" class="form-control"
               value="{{ old('return_time', $p?->return_time) }}">
      </div>
    </div>
    <div class="form-group mb-0">
      <label class="font-weight-bold">Maksud / Tujuan Perjalanan</label>
      <textarea name="purpose" class="form-control" rows="2" placeholder="Jelaskan keperluan perjalanan dinas">{{ old('purpose', $p?->purpose) }}</textarea>
    </div>
  </div>
</div>

{{-- 2. Rincian Anggaran --}}
<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="font-weight-bold">2. Rincian Anggaran</span>
    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-budget">
      <i class="gd-plus mr-1"></i> Tambah Item
    </button>
  </div>
  <div class="card-body">
    <p class="small text-muted mb-2">
      Item dengan penanggung <strong>By GA</strong> diatur dan dibayarkan langsung oleh bagian General Affairs.
      Uang saku = jumlah hari &times; nominal per hari.
    </p>
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0" style="min-width:840px;font-size:.85rem">
        <thead class="thead-light">
          <tr>
            <th style="min-width:130px">Kategori</th>
            <th style="min-width:170px">Nama Item</th>
            <th style="min-width:100px">Ditanggung</th>
            <th class="text-right" style="width:70px">Qty</th>
            <th class="text-right" style="min-width:120px">Biaya Satuan</th>
            <th class="text-right" style="min-width:120px">Total</th>
            <th style="width:40px"></th>
          </tr>
        </thead>
        <tbody id="budget-body"></tbody>
        <tfoot>
          <tr class="table-light font-weight-bold">
            <td colspan="5" class="text-right">Total Ditanggung Sendiri</td>
            <td class="text-right" id="total-self">Rp 0</td>
            <td></td>
          </tr>
          <tr class="table-light font-weight-bold">
            <td colspan="5" class="text-right">Total Anggaran</td>
            <td class="text-right" id="total-all">Rp 0</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- 3. Itinerary --}}
<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="font-weight-bold">3. Itinerary / Rencana Perjalanan</span>
    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-iti">
      <i class="gd-plus mr-1"></i> Tambah Baris
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0" style="min-width:780px;font-size:.85rem">
        <thead class="thead-light">
          <tr>
            <th style="width:40px">No</th>
            <th style="min-width:140px">Tanggal</th>
            <th style="min-width:90px">Jam Mulai</th>
            <th style="min-width:90px">Jam Selesai</th>
            <th style="min-width:90px">Zona</th>
            <th style="min-width:200px">Keterangan</th>
            <th style="width:40px"></th>
          </tr>
        </thead>
        <tbody id="iti-body"></tbody>
      </table>
    </div>
  </div>
</div>

<button type="submit" class="btn btn-outline-secondary">
  <i class="gd-save mr-1"></i> {{ $editing ? 'Simpan Perubahan' : 'Simpan Draft' }}
</button>

@php
  // Seed budget items
  $budgetJson = 'null';
  if (old('budget')) {
    $budgetJson = collect(old('budget'))->values()->toJson();
  } elseif ($editing && $p && $p->budgetItems->isNotEmpty()) {
    $budgetJson = $p->budgetItems->map(fn($i) => [
      'category'   => $i->category,
      'item_name'  => $i->item_name,
      'handled_by' => $i->handled_by,
      'qty'        => (int) $i->qty,
      'unit_cost'  => (int) $i->unit_cost,
    ])->toJson();
  }

  // Seed itinerary
  $itiJson = 'null';
  if (old('itinerary')) {
    $itiJson = collect(old('itinerary'))->values()->toJson();
  } elseif ($editing && $p && $p->itineraries->isNotEmpty()) {
    $itiJson = $p->itineraries->map(fn($i) => [
      'travel_date' => $i->travel_date->format('Y-m-d'),
      'time_start'  => $i->time_start,
      'time_end'    => $i->time_end,
      'timezone'    => $i->timezone,
      'description' => $i->description,
    ])->toJson();
  }
@endphp
<script>
  window.__perdinBudget = {!! $budgetJson !!};
  window.__perdinItinerary = {!! $itiJson !!};
  window.__perdinCategories = {!! json_encode($catLabels) !!};
</script>
