@php
  $editing   = $editing ?? false;
  $reimb     = $reimbursement ?? null;
  $amtLabels = \App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS;
@endphp

{{-- Error summary --}}
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

{{-- Informasi Pengajuan --}}
<div class="card mb-3">
  <div class="card-header font-weight-bold">Informasi Pengajuan</div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-group col-md-4">
        <label class="font-weight-bold">Tanggal Pengajuan <span class="text-danger">*</span></label>
        <input type="date" name="request_date" class="form-control @error('request_date') is-invalid @enderror"
               value="{{ old('request_date', $reimb?->request_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               max="{{ now()->format('Y-m-d') }}" required>
        @error('request_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group col-md-4">
        <label class="font-weight-bold">Pengobatan Untuk <span class="text-danger">*</span></label>
        <select name="medical_for" class="form-control @error('medical_for') is-invalid @enderror" required>
          @foreach(\App\Models\Reimbursement\ReimbursementRequest::$medicalForLabels as $val => $label)
            <option value="{{ $val }}" {{ old('medical_for', $reimb?->medical_for) === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        @error('medical_for')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group col-md-4">
        <label class="font-weight-bold">Status Pernikahan <span class="text-danger">*</span></label>
        <select name="marital_status" class="form-control @error('marital_status') is-invalid @enderror" required>
          <option value="single"  {{ old('marital_status', $reimb?->marital_status) === 'single'  ? 'selected' : '' }}>Lajang</option>
          <option value="married" {{ old('marital_status', $reimb?->marital_status) === 'married' ? 'selected' : '' }}>Menikah</option>
        </select>
        @error('marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="form-group mb-0">
      <label class="font-weight-bold">Catatan</label>
      <textarea name="notes" class="form-control" rows="2" placeholder="Opsional">{{ old('notes', $reimb?->notes) }}</textarea>
    </div>
  </div>
</div>

{{-- Rincian Biaya --}}
<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="font-weight-bold">Rincian Biaya</span>
    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-row">
      <i class="gd-plus mr-1"></i> Tambah Baris
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0" id="items-table" style="min-width:1400px;font-size:.82rem">
        <thead class="thead-light">
          <tr>
            <th style="min-width:130px">Nama Pasien</th>
            <th style="min-width:110px">Tgl Berobat</th>
            <th style="min-width:150px">Faskes / RS</th>
            <th style="min-width:130px">Diagnosa</th>
            @foreach($amtLabels as $key => $lbl)
              <th class="text-right" style="min-width:90px">{{ $lbl }}</th>
            @endforeach
            <th class="text-right" style="min-width:110px">Total</th>
            <th style="width:40px"></th>
          </tr>
        </thead>
        <tbody id="items-body"></tbody>
        <tfoot>
          <tr class="table-light font-weight-bold">
            <td colspan="4" class="text-right">Grand Total</td>
            @foreach($amtLabels as $key => $lbl)
              <td class="text-right col-sum" data-field="{{ $key }}">0</td>
            @endforeach
            <td class="text-right" id="grand-total">Rp 0</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- Existing Attachments (edit mode) --}}
@if($editing && $reimb->attachments->isNotEmpty())
<div class="card mb-3">
  <div class="card-header font-weight-bold">Lampiran yang Ada</div>
  <div class="card-body py-2">
    @foreach($reimb->attachments as $att)
    <a href="{{ route('reimbursement.attachment', [$reimb, $att]) }}" target="_blank"
       class="badge badge-secondary mr-1 mb-1" style="font-size:.85rem;padding:.4em .7em">
      <i class="gd-clip mr-1"></i>{{ $att->file_name }}
    </a>
    @endforeach
  </div>
</div>
@endif

{{-- Upload Lampiran --}}
<div class="card mb-3">
  <div class="card-header font-weight-bold">Upload Kwitansi / Bukti</div>
  <div class="card-body">
    <input type="file" name="attachments[]" class="form-control-file" multiple accept=".jpg,.jpeg,.png,.pdf">
    <small class="text-muted">Format: JPG, PNG, PDF. Maks 5 MB per file. Bisa pilih beberapa file sekaligus.</small>
  </div>
</div>

<button type="submit" class="btn btn-outline-secondary">
  <i class="gd-save mr-1"></i> Simpan Draft
</button>

{{-- Seed items: old() → DB items (edit) → kosong --}}
@php
  $existingItemsJson = 'null';
  if (old('items')) {
    $existingItemsJson = collect(old('items'))->values()->map(function($item) use ($amtLabels) {
      $data = [
        'patient_name'   => $item['patient_name']   ?? '',
        'treatment_date' => $item['treatment_date'] ?? '',
        'institution'    => $item['institution']    ?? '',
        'diagnose'       => $item['diagnose']       ?? '',
      ];
      foreach (array_keys($amtLabels) as $f) { $data[$f] = isset($item[$f]) ? (int)$item[$f] : 0; }
      return $data;
    })->toJson();
  } elseif ($editing && $reimb && $reimb->items->isNotEmpty()) {
    $existingItemsJson = $reimb->items->map(function($item) use ($amtLabels) {
      $data = [
        'patient_name'   => $item->patient_name,
        'treatment_date' => $item->treatment_date->format('Y-m-d'),
        'institution'    => $item->institution,
        'diagnose'       => $item->diagnose ?? '',
      ];
      foreach (array_keys($amtLabels) as $f) { $data[$f] = (int)$item->$f; }
      return $data;
    })->toJson();
  }
@endphp
<script>window.__existingItems = {!! $existingItemsJson !!};</script>
