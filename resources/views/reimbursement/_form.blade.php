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

{{-- Upload Dokumen Pendukung --}}
@php $docTypes = \App\Models\Reimbursement\ReimbursementAttachment::$docTypes; @endphp
<div class="card mb-3">
  <div class="card-header font-weight-bold">Dokumen Pendukung</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="thead-light">
        <tr>
          <th style="width:220px">Jenis Dokumen</th>
          <th>File</th>
        </tr>
      </thead>
      <tbody>
        @foreach($docTypes as $type => $label)
        @php $existing = ($editing && $reimb) ? $reimb->attachments->firstWhere('doc_type', $type) : null; @endphp
        <tr>
          <td class="align-middle font-weight-bold" style="font-size:.88rem">{{ $label }}</td>
          <td class="align-middle">
            @if($existing)
              <div class="d-flex align-items-center flex-wrap mb-1" style="gap:.4rem">
                <a href="{{ route('reimbursement.attachment', [$reimb, $existing]) }}" target="_blank"
                   class="btn btn-xs btn-outline-success">
                  <i class="gd-clip mr-1"></i>{{ $existing->file_name }}
                </a>
                <form method="POST"
                      action="{{ route('reimbursement.attachment.destroy', [$reimb, $existing]) }}"
                      onsubmit="return confirm('Hapus lampiran ini?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-xs btn-outline-danger">
                    <i class="gd-trash"></i>
                  </button>
                </form>
                <small class="text-muted">Unggah baru untuk mengganti</small>
              </div>
            @endif
            <div class="custom-file" style="max-width:340px">
              <input type="file" name="attachments[{{ $type }}]"
                     id="att_{{ $type }}"
                     class="custom-file-input"
                     accept=".jpg,.jpeg,.png,.pdf">
              <label class="custom-file-label" for="att_{{ $type }}">Pilih file</label>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-3 py-2"><small class="text-muted">Format: JPG, PNG, PDF. Maks 5 MB per file. Tidak wajib semua diisi.</small></div>
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
