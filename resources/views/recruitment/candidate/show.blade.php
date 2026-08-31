@extends('layouts.grain')
@section('title', 'Detail Kandidat')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('recruitment.candidates.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-7">

    <div class="card mb-4">
      <div class="card-header font-weight-bold d-flex justify-content-between">
        <span>{{ $candidate->name }}</span>
        <span class="badge badge-{{ \App\Models\Candidate::$statusBadges[$candidate->status] ?? 'secondary' }}">
          {{ \App\Models\Candidate::$statusLabels[$candidate->status] ?? $candidate->status }}
        </span>
      </div>
      <div class="card-body">
        <dl class="row mb-3">
          <dt class="col-sm-4 text-muted">Email</dt><dd class="col-sm-8">{{ $candidate->email ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Telepon</dt><dd class="col-sm-8">{{ $candidate->phone ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Requisition</dt><dd class="col-sm-8">{{ $candidate->jobRequisition?->title ?? 'Walk-in' }}</dd>
          <dt class="col-sm-4 text-muted">Sumber</dt><dd class="col-sm-8">{{ $candidate->source ?? '—' }}</dd>
          @if($candidate->notes)
            <dt class="col-sm-4 text-muted">Catatan</dt><dd class="col-sm-8">{{ $candidate->notes }}</dd>
          @endif
          @if($candidate->isConverted())
            <dt class="col-sm-4 text-muted">Karyawan</dt>
            <dd class="col-sm-8"><a href="{{ route('appraisal.employees.edit', $candidate->convertedEmployee) }}">{{ $candidate->convertedEmployee?->name }}</a></dd>
          @endif
        </dl>

        @if(!$candidate->isConverted())
        <form method="POST" action="{{ route('recruitment.candidates.status', $candidate) }}" class="form-row align-items-end">
          @csrf
          <div class="form-group col-md-5">
            <label class="small">Ubah Status</label>
            <select name="status" class="form-control form-control-sm">
              @foreach(\App\Models\Candidate::$statusLabels as $key => $label)
                @continue($key === 'converted')
                <option value="{{ $key }}" @selected($candidate->status === $key)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4">
            <label class="small">Hasil MCU</label>
            <select name="mcu_result" class="form-control form-control-sm">
              <option value="">-- Belum MCU --</option>
              <option value="fit" @selected($candidate->mcu_result === 'fit')>Fit</option>
              <option value="unfit" @selected($candidate->mcu_result === 'unfit')>Unfit</option>
              <option value="conditional" @selected($candidate->mcu_result === 'conditional')>Conditional</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <button type="submit" class="btn btn-sm btn-primary btn-block">Update</button>
          </div>
        </form>
        @endif

        @if($candidate->isAccepted())
          <hr>
          <div class="font-weight-bold small mb-2">Konversi jadi Karyawan (Pre-Employment → Employee)</div>
          <form method="POST" action="{{ route('recruitment.candidates.convert', $candidate) }}">
            @csrf
            <div class="form-row">
              <div class="form-group col-md-4">
                <label class="small">Perusahaan <span class="text-danger">*</span></label>
                <select name="company_id" class="form-control form-control-sm" required>
                  @foreach(\App\Models\Company::where('is_active', true)->orderBy('name')->get() as $c)
                    <option value="{{ $c->id }}" @selected($candidate->jobRequisition?->company_id === $c->id)>{{ $c->short_name ?? $c->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-4">
                <label class="small">Jabatan</label>
                <select name="position_id" class="form-control form-control-sm">
                  <option value="">-- Pilih --</option>
                  @foreach($positions as $p)
                    <option value="{{ $p->id }}" @selected($candidate->jobRequisition?->position_id === $p->id)>{{ $p->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-4">
                <label class="small">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control form-control-sm" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label class="small">Departemen</label>
                <select name="department_id" class="form-control form-control-sm">
                  <option value="">-- Pilih --</option>
                  @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $d)
                    <option value="{{ $d->id }}" @selected($candidate->jobRequisition?->department_id === $d->id)>{{ $d->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-4">
                <label class="small">Status Kewarganegaraan <span class="text-danger">*</span></label>
                <select name="employee_type" class="form-control form-control-sm" required>
                  <option value="local">Local</option>
                  <option value="expat">Expat</option>
                </select>
              </div>
              <div class="form-group col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-success btn-block" onclick="return confirm('Konversi kandidat ini jadi karyawan?')">
                  <i class="gd-check mr-1"></i> Konversi jadi Karyawan
                </button>
              </div>
            </div>
          </form>
        @endif

        @if(!$candidate->isConverted())
          <form method="POST" action="{{ route('recruitment.candidates.destroy', $candidate) }}" class="mt-2" onsubmit="return confirm('Hapus kandidat ini?')">
            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button>
          </form>
        @endif
      </div>
    </div>

    {{-- Interviews --}}
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Interview</div>
      <div class="card-body">
        @foreach($candidate->interviews as $iv)
          <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
              <div class="font-weight-bold small">{{ $iv->stage }}</div>
              <div class="small text-muted">
                {{ optional($iv->scheduled_at)->format('d/m/Y H:i') ?? 'Belum dijadwalkan' }}
                @if($iv->interviewer) · {{ $iv->interviewer->name }} @endif
              </div>
            </div>
            <span class="badge badge-{{ $iv->result === 'pass' ? 'success' : ($iv->result === 'fail' ? 'danger' : 'secondary') }}">
              {{ \App\Models\CandidateInterview::$resultLabels[$iv->result] ?? $iv->result }}
            </span>
          </div>
        @endforeach

        @unless($candidate->isConverted())
        <form method="POST" action="{{ route('recruitment.candidates.interviews.store', $candidate) }}" class="form-row align-items-end mt-3">
          @csrf
          <div class="form-group col-md-4 mb-2">
            <input type="text" name="stage" class="form-control form-control-sm" placeholder="Tahap (mis. HR Screening)" required>
          </div>
          <div class="form-group col-md-3 mb-2">
            <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm">
          </div>
          <div class="form-group col-md-3 mb-2">
            <select name="result" class="form-control form-control-sm">
              <option value="pending">Menunggu</option>
              <option value="pass">Lolos</option>
              <option value="fail">Tidak Lolos</option>
            </select>
          </div>
          <div class="form-group col-md-2 mb-2">
            <button type="submit" class="btn btn-sm btn-outline-primary btn-block">Tambah</button>
          </div>
        </form>
        @endunless
      </div>
    </div>

    {{-- Offers --}}
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Penawaran (Offer)</div>
      <div class="card-body">
        @foreach($candidate->offers as $of)
          <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
              <div class="font-weight-bold small">{{ $of->position?->name ?? '—' }} — Rp {{ number_format((int) $of->offered_salary, 0, ',', '.') }}</div>
              <div class="small text-muted">Mulai {{ optional($of->start_date_offered)->format('d/m/Y') ?? '—' }}</div>
            </div>
            <span class="badge badge-secondary">{{ \App\Models\CandidateOffer::$statusLabels[$of->status] ?? $of->status }}</span>
          </div>
        @endforeach

        @unless($candidate->isConverted())
        <form method="POST" action="{{ route('recruitment.candidates.offers.store', $candidate) }}" class="form-row align-items-end mt-3">
          @csrf
          <div class="form-group col-md-3 mb-2">
            <select name="position_id" class="form-control form-control-sm">
              <option value="">-- Jabatan --</option>
              @foreach($positions as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
            </select>
          </div>
          <div class="form-group col-md-3 mb-2">
            <input type="number" data-rupiah name="offered_salary" class="form-control form-control-sm" placeholder="Gaji ditawarkan">
          </div>
          <div class="form-group col-md-2 mb-2">
            <input type="date" name="start_date_offered" class="form-control form-control-sm">
          </div>
          <div class="form-group col-md-2 mb-2">
            <select name="status" class="form-control form-control-sm">
              <option value="draft">Draft</option>
              <option value="sent">Terkirim</option>
              <option value="accepted">Diterima</option>
              <option value="declined">Ditolak</option>
            </select>
          </div>
          <div class="form-group col-md-2 mb-2">
            <button type="submit" class="btn btn-sm btn-outline-primary btn-block">Tambah</button>
          </div>
        </form>
        @endunless
      </div>
    </div>

    {{-- Documents (Pre-Employment) --}}
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Dokumen Pre-Employment</div>
      <div class="card-body">
        @foreach($candidate->documents as $doc)
          <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
              <div class="font-weight-bold small">{{ $doc->title }}</div>
              <div class="small text-muted">{{ \App\Models\CandidateDocument::$docTypes[$doc->doc_type] ?? $doc->doc_type }}</div>
            </div>
            <div>
              <a href="{{ route('recruitment.candidates.documents.download', [$candidate, $doc]) }}" class="btn btn-xs btn-outline-secondary"><i class="gd-download"></i></a>
              <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus dokumen {{ $doc->title }}?" data-confirm-title="Hapus Dokumen" data-form="cd-del-{{ $doc->id }}"><i class="gd-trash"></i></a>
              <form id="cd-del-{{ $doc->id }}" method="POST" action="{{ route('recruitment.candidates.documents.destroy', [$candidate, $doc]) }}" class="d-none">@csrf @method('DELETE')</form>
            </div>
          </div>
        @endforeach

        <form method="POST" action="{{ route('recruitment.candidates.documents.store', $candidate) }}" enctype="multipart/form-data" class="form-row align-items-end mt-3">
          @csrf
          <div class="form-group col-md-3 mb-2">
            <select name="doc_type" class="form-control form-control-sm" required>
              @foreach(\App\Models\CandidateDocument::$docTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4 mb-2">
            <input type="text" name="title" class="form-control form-control-sm" placeholder="Judul dokumen" required>
          </div>
          <div class="form-group col-md-3 mb-2">
            <input type="file" name="file" class="form-control form-control-sm" required>
          </div>
          <div class="form-group col-md-2 mb-2">
            <button type="submit" class="btn btn-sm btn-outline-primary btn-block">Unggah</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
@endsection
