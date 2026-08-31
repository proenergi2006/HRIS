@extends('layouts.grain')
@section('title', 'Detail Kandidat')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('recruitment.candidates.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-7">

    <div class="card mb-4">
      <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
        <span>{{ $candidate->name }}</span>
        @php $stg = $candidate->stage(); @endphp
        <span>
          <span class="badge badge-{{ $stg['badge'] }}">{{ $stg['label'] }}</span>
          <span class="badge badge-light border text-muted">{{ \App\Models\Candidate::$statusLabels[$candidate->status] ?? $candidate->status }}</span>
        </span>
      </div>
      <div class="px-3 pt-2">
        @php $stages = ['candidate' => 'Kandidat', 'hired' => 'Hired', 'preemp' => 'Pre-Employment', 'joined' => 'Joined'];
             $curKey = in_array($stg['key'], ['preemp_done']) ? 'preemp' : $stg['key'];
             $order = array_keys($stages); $curIdx = array_search($curKey, $order); @endphp
        <div class="d-flex text-center small">
          @foreach($stages as $k => $lbl)
            @php $i = array_search($k, $order); $state = $curIdx === false ? 'muted' : ($i < $curIdx ? 'done' : ($i === $curIdx ? 'cur' : 'todo')); @endphp
            <div class="flex-fill">
              <div class="mx-auto rounded-circle {{ $state === 'done' ? 'bg-success' : ($state === 'cur' ? 'bg-primary' : 'bg-light border') }}"
                   style="width:14px;height:14px"></div>
              <div class="{{ $state === 'todo' || $state === 'muted' ? 'text-muted' : 'font-weight-bold' }}">{{ $lbl }}</div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="card-body">
        <dl class="row mb-3">
          <dt class="col-sm-4 text-muted">Email</dt><dd class="col-sm-8">{{ $candidate->email ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Telepon</dt><dd class="col-sm-8">{{ $candidate->phone ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Requisition</dt><dd class="col-sm-8">{{ $candidate->jobRequisition?->title ?? 'Walk-in' }}</dd>
          <dt class="col-sm-4 text-muted">Sumber</dt><dd class="col-sm-8">{{ $candidate->source ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Ekspektasi Gaji</dt>
          <dd class="col-sm-8">{{ $candidate->expected_salary ? 'Rp ' . number_format($candidate->expected_salary, 0, ',', '.') : '—' }}</dd>
          <dt class="col-sm-4 text-muted">Hasil Assessment</dt>
          <dd class="col-sm-8">
            @if($candidate->assessment_result)
              <span class="badge badge-{{ $candidate->assessment_result === 'pass' ? 'success' : ($candidate->assessment_result === 'fail' ? 'danger' : 'warning') }}">
                {{ \App\Models\Candidate::$assessmentLabels[$candidate->assessment_result] ?? $candidate->assessment_result }}
              </span>
              @if($candidate->assessment_score !== null) · skor {{ rtrim(rtrim(number_format($candidate->assessment_score, 2), '0'), ',') }}@endif
              @if($candidate->assessment_notes)<div class="small text-muted mt-1">{{ $candidate->assessment_notes }}</div>@endif
            @else — @endif
          </dd>
          <dt class="col-sm-4 text-muted">Hasil MCU</dt>
          <dd class="col-sm-8">{{ $candidate->mcu_result ? ucfirst($candidate->mcu_result) : '—' }}</dd>
          @if($candidate->notes)
            <dt class="col-sm-4 text-muted">Catatan</dt><dd class="col-sm-8">{{ $candidate->notes }}</dd>
          @endif
          @if($candidate->isConverted())
            <dt class="col-sm-4 text-muted">Karyawan</dt>
            <dd class="col-sm-8"><a href="{{ route('appraisal.employees.edit', $candidate->convertedEmployee) }}">{{ $candidate->convertedEmployee?->name }}</a></dd>
          @endif
        </dl>

        @if(!$candidate->isConverted())
        <form method="POST" action="{{ route('recruitment.candidates.status', $candidate) }}" class="form-row">
          @csrf
          <div class="form-group col-md-4">
            <label class="small">Ubah Status</label>
            <select name="status" class="form-control form-control-sm">
              @foreach(\App\Models\Candidate::$statusLabels as $key => $label)
                @continue($key === 'converted')
                <option value="{{ $key }}" @selected($candidate->status === $key)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4">
            <label class="small">Hasil Assessment</label>
            <select name="assessment_result" class="form-control form-control-sm">
              <option value="">-- Belum --</option>
              @foreach(\App\Models\Candidate::$assessmentLabels as $key => $label)
                <option value="{{ $key }}" @selected($candidate->assessment_result === $key)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4">
            <label class="small">Skor Assessment (0–100)</label>
            <input type="number" step="0.01" min="0" max="100" name="assessment_score" class="form-control form-control-sm"
                   value="{{ $candidate->assessment_score }}">
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
          <div class="form-group col-md-6">
            <label class="small">Catatan Assessment</label>
            <input type="text" name="assessment_notes" class="form-control form-control-sm"
                   value="{{ $candidate->assessment_notes }}" placeholder="mis. psikotes, skill test, panel">
          </div>
          <div class="form-group col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-sm btn-primary btn-block">Update</button>
          </div>
        </form>
        @endif

        @if($candidate->isAccepted())
          <hr>
          <div class="font-weight-bold small mb-2">Konversi jadi Karyawan (Pre-Employment → Employee)</div>
          @php $peMissing = $candidate->preEmploymentMissing(); @endphp
          @if($peMissing->isNotEmpty())
            <div class="alert alert-warning py-2 px-3 small">
              <i class="gd-alert mr-1"></i><strong>Pre-Employment belum lengkap.</strong>
              Item wajib belum selesai: {{ $peMissing->implode(', ') }}.
              Lengkapi di panel <strong>Pre-Employment</strong> di bawah.
            </div>
          @endif
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
              <div class="form-group col-md-4">
                <label class="small">Jenis Perjanjian Kerja <span class="text-danger">*</span></label>
                <select name="contract_type" class="form-control form-control-sm" required>
                  <option value="probation">Probation</option>
                  <option value="pkwt">PKWT (Kontrak)</option>
                  <option value="pkwtt">PKWTT (Tetap)</option>
                  <option value="magang">Magang</option>
                </select>
              </div>
            </div>
            <div class="form-row align-items-end">
              <div class="form-group col-md-3">
                <label class="small">Durasi Probation/Kontrak (bln)</label>
                <input type="number" name="probation_months" class="form-control form-control-sm" value="3" min="1" max="24">
                <small class="form-text text-muted">Diabaikan untuk PKWTT.</small>
              </div>
              <div class="form-group col-md-4">
                <div class="custom-control custom-checkbox mb-2">
                  <input type="checkbox" class="custom-control-input" id="create_account" name="create_account" value="1"
                         {{ $candidate->email ? '' : 'disabled' }}>
                  <label class="custom-control-label small" for="create_account">
                    Buatkan akun login (role karyawan){{ $candidate->email ? '' : ' — email kandidat kosong' }}
                  </label>
                </div>
              </div>
              <div class="form-group col-md-5">
                <button type="submit" class="btn btn-sm btn-success btn-block" @disabled($peMissing->isNotEmpty())
                        onclick="return confirm('Konversi kandidat ini jadi karyawan? NIP & perjanjian kerja dibuat otomatis.')">
                  <i class="gd-check mr-1"></i> Konversi jadi Karyawan (Joined)
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

    @php $lockProfile = $candidate->isConverted(); @endphp

    {{-- Pre-Employment --}}
    @php $pe = $candidate->preEmployment; $peProg = $candidate->preEmploymentProgress(); @endphp
    <div class="card mb-4 border-primary">
      <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
        <span>Pre-Employment Checklist</span>
        <span class="small">{{ $peProg['done'] }}/{{ $peProg['total'] }} selesai</span>
      </div>
      <div class="card-body">
        <div class="progress mb-3" style="height:8px">
          <div class="progress-bar {{ $peProg['pct'] == 100 ? 'bg-success' : 'bg-warning' }}" style="width:{{ $peProg['pct'] }}%"></div>
        </div>

        <ul class="list-unstyled mb-0">
          @foreach($candidate->preEmploymentTasks->sortBy('item.sort_order') as $task)
            <li class="d-flex align-items-center border-bottom py-2">
              <form method="POST" action="{{ route('recruitment.candidates.preemployment.toggle', [$candidate, $task]) }}" class="mr-2">
                @csrf
                <button type="submit" class="btn btn-xs {{ $task->is_done ? 'btn-success' : 'btn-outline-secondary' }}" @disabled($lockProfile)>
                  <i class="gd-check"></i>
                </button>
              </form>
              <div class="flex-grow-1">
                <span class="{{ $task->is_done ? 'text-muted' : 'font-weight-bold' }} small">{{ $task->item?->label }}</span>
                @if($task->item?->is_required)<span class="badge badge-light border ml-1">wajib</span>@endif
                <span class="badge badge-light text-muted ml-1">{{ \App\Models\PreEmploymentChecklistItem::$categoryLabels[$task->item?->category] ?? $task->item?->category }}</span>
                @if($task->is_done && $task->done_at)<div class="small text-muted">Selesai {{ $task->done_at->format('d/m/Y') }} · {{ $task->doneBy?->name }}</div>@endif
              </div>
            </li>
          @endforeach
        </ul>

        {{-- Data terstruktur --}}
        <hr>
        <form method="POST" action="{{ route('recruitment.candidates.preemployment.update', $candidate) }}">
          @csrf @method('PUT')
          <div class="font-weight-bold small text-muted mb-2">DATA PRIBADI</div>
          <div class="form-row">
            <div class="form-group col-md-3">
              <label class="small">Jenis Kelamin</label>
              <select name="gender" class="form-control form-control-sm" @disabled($lockProfile)>
                <option value="">—</option>
                <option value="L" @selected($pe?->gender === 'L')>Laki-laki</option>
                <option value="P" @selected($pe?->gender === 'P')>Perempuan</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Tempat Lahir</label>
              <input name="birth_place" class="form-control form-control-sm" value="{{ $pe?->birth_place }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Tanggal Lahir</label>
              <input type="date" name="birth_date" class="form-control form-control-sm" value="{{ optional($pe?->birth_date)->format('Y-m-d') }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Status Kawin</label>
              <select name="marital_status_id" class="form-control form-control-sm" @disabled($lockProfile)>
                <option value="">—</option>
                @foreach($maritalStatuses as $ms)<option value="{{ $ms->id }}" @selected($pe?->marital_status_id == $ms->id)>{{ $ms->name }}</option>@endforeach
              </select>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Agama</label>
              <select name="religion_id" class="form-control form-control-sm" @disabled($lockProfile)>
                <option value="">—</option>
                @foreach($religions as $rl)<option value="{{ $rl->id }}" @selected($pe?->religion_id == $rl->id)>{{ $rl->name }}</option>@endforeach
              </select>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Golongan Darah</label>
              <select name="blood_type_id" class="form-control form-control-sm" @disabled($lockProfile)>
                <option value="">—</option>
                @foreach($bloodTypes as $bt)<option value="{{ $bt->id }}" @selected($pe?->blood_type_id == $bt->id)>{{ $bt->name }}</option>@endforeach
              </select>
            </div>
          </div>

          <div class="font-weight-bold small text-muted mb-2 mt-2">IDENTITAS</div>
          <div class="form-row">
            <div class="form-group col-md-3">
              <label class="small">No. KTP</label>
              <input name="ktp_number" class="form-control form-control-sm" value="{{ $pe?->ktp_number }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-3">
              <label class="small">No. NPWP</label>
              <input name="npwp_number" class="form-control form-control-sm" value="{{ $pe?->npwp_number }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-6">
              <label class="small">Alamat KTP</label>
              <input name="ktp_address" class="form-control form-control-sm" value="{{ $pe?->ktp_address }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Kota KTP</label>
              <input name="ktp_city" class="form-control form-control-sm" value="{{ $pe?->ktp_city }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-6">
              <label class="small">Alamat Domisili</label>
              <input name="domicile_address" class="form-control form-control-sm" value="{{ $pe?->domicile_address }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Kota Domisili</label>
              <input name="domicile_city" class="form-control form-control-sm" value="{{ $pe?->domicile_city }}" @disabled($lockProfile)>
            </div>
          </div>

          <div class="font-weight-bold small text-muted mb-2 mt-2">REKENING BANK</div>
          <div class="form-row">
            <div class="form-group col-md-4">
              <label class="small">Bank</label>
              <select name="bank_id" class="form-control form-control-sm" @disabled($lockProfile)>
                <option value="">—</option>
                @foreach($banks as $bk)<option value="{{ $bk->id }}" @selected($pe?->bank_id == $bk->id)>{{ $bk->name }}</option>@endforeach
              </select>
            </div>
            <div class="form-group col-md-4">
              <label class="small">No. Rekening</label>
              <input name="bank_account_number" class="form-control form-control-sm" value="{{ $pe?->bank_account_number }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-4">
              <label class="small">Atas Nama</label>
              <input name="bank_account_holder" class="form-control form-control-sm" value="{{ $pe?->bank_account_holder }}" @disabled($lockProfile)>
            </div>
          </div>

          <div class="font-weight-bold small text-muted mb-2 mt-2">BPJS</div>
          <div class="form-row">
            <div class="form-group col-md-3">
              <label class="small">BPJS Kesehatan — No.</label>
              <input name="bpjs_health_number" class="form-control form-control-sm" value="{{ $pe?->bpjs_health_number }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Tgl Daftar Kesehatan</label>
              <input type="date" name="bpjs_health_date" class="form-control form-control-sm" value="{{ optional($pe?->bpjs_health_date)->format('Y-m-d') }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-3">
              <label class="small">BPJS TK — No.</label>
              <input name="bpjs_employment_number" class="form-control form-control-sm" value="{{ $pe?->bpjs_employment_number }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-3">
              <label class="small">Tgl Daftar TK</label>
              <input type="date" name="bpjs_employment_date" class="form-control form-control-sm" value="{{ optional($pe?->bpjs_employment_date)->format('Y-m-d') }}" @disabled($lockProfile)>
            </div>
          </div>

          <div class="font-weight-bold small text-muted mb-2 mt-2">KONTAK DARURAT</div>
          <div class="form-row">
            <div class="form-group col-md-4">
              <label class="small">Nama</label>
              <input name="emergency_contact_name" class="form-control form-control-sm" value="{{ $pe?->emergency_contact_name }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-4">
              <label class="small">Hubungan</label>
              <input name="emergency_contact_relation" class="form-control form-control-sm" value="{{ $pe?->emergency_contact_relation }}" @disabled($lockProfile)>
            </div>
            <div class="form-group col-md-4">
              <label class="small">Telepon</label>
              <input name="emergency_contact_phone" class="form-control form-control-sm" value="{{ $pe?->emergency_contact_phone }}" @disabled($lockProfile)>
            </div>
          </div>

          @unless($lockProfile)
          <button class="btn btn-sm btn-primary mt-2">Simpan Data Pre-Employment</button>
          @endunless
        </form>
      </div>
    </div>

    {{-- Pendidikan --}}
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Pendidikan</div>
      <div class="card-body">
        @forelse($candidate->educations as $ed)
          <div class="d-flex justify-content-between align-items-start border-bottom py-2">
            <div>
              <div class="font-weight-bold small">{{ $ed->education_level ?: '—' }} {{ $ed->major ? '· ' . $ed->major : '' }}</div>
              <div class="small text-muted">{{ $ed->institution ?: '—' }}
                @if($ed->graduation_year) · lulus {{ $ed->graduation_year }}@endif
                @if($ed->gpa) · IPK {{ $ed->gpa }}@endif</div>
              @if($ed->notes)<div class="small font-italic">{{ $ed->notes }}</div>@endif
            </div>
            @unless($lockProfile)
            <form method="POST" action="{{ route('recruitment.candidates.profile.destroy', [$candidate, 'education', $ed->id]) }}" onsubmit="return confirm('Hapus?')">
              @csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="gd-trash"></i></button>
            </form>
            @endunless
          </div>
        @empty
          <p class="text-muted small mb-0">Belum ada data pendidikan.</p>
        @endforelse

        @unless($lockProfile)
        <form method="POST" action="{{ route('recruitment.candidates.profile.store', [$candidate, 'education']) }}" class="form-row mt-3">
          @csrf
          <div class="form-group col-md-2 mb-2"><input name="education_level" class="form-control form-control-sm" placeholder="Jenjang (S1)"></div>
          <div class="form-group col-md-3 mb-2"><input name="major" class="form-control form-control-sm" placeholder="Jurusan"></div>
          <div class="form-group col-md-3 mb-2"><input name="institution" class="form-control form-control-sm" placeholder="Institusi"></div>
          <div class="form-group col-md-2 mb-2"><input type="number" name="graduation_year" class="form-control form-control-sm" placeholder="Thn lulus"></div>
          <div class="form-group col-md-1 mb-2"><input type="number" step="0.01" name="gpa" class="form-control form-control-sm" placeholder="IPK"></div>
          <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-outline-primary btn-block">+</button></div>
        </form>
        @endunless
      </div>
    </div>

    {{-- Pengalaman Kerja --}}
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Pengalaman Kerja</div>
      <div class="card-body">
        @forelse($candidate->experiences as $xp)
          <div class="d-flex justify-content-between align-items-start border-bottom py-2">
            <div>
              <div class="font-weight-bold small">{{ $xp->job_title ?: '—' }} · {{ $xp->company_name }}</div>
              <div class="small text-muted">
                {{ optional($xp->start_date)->format('m/Y') ?? '?' }} – {{ optional($xp->end_date)->format('m/Y') ?? 'sekarang' }}
                @if($xp->company_city) · {{ $xp->company_city }}@endif
                @if($xp->last_salary) · gaji akhir Rp {{ number_format($xp->last_salary, 0, ',', '.') }}@endif
              </div>
              @if($xp->job_description)<div class="small font-italic">{{ $xp->job_description }}</div>@endif
            </div>
            @unless($lockProfile)
            <form method="POST" action="{{ route('recruitment.candidates.profile.destroy', [$candidate, 'experience', $xp->id]) }}" onsubmit="return confirm('Hapus?')">
              @csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="gd-trash"></i></button>
            </form>
            @endunless
          </div>
        @empty
          <p class="text-muted small mb-0">Belum ada pengalaman kerja.</p>
        @endforelse

        @unless($lockProfile)
        <form method="POST" action="{{ route('recruitment.candidates.profile.store', [$candidate, 'experience']) }}" class="form-row mt-3">
          @csrf
          <div class="form-group col-md-4 mb-2"><input name="company_name" class="form-control form-control-sm" placeholder="Nama perusahaan *" required></div>
          <div class="form-group col-md-3 mb-2"><input name="job_title" class="form-control form-control-sm" placeholder="Jabatan"></div>
          <div class="form-group col-md-2 mb-2"><input type="date" name="start_date" class="form-control form-control-sm" title="Mulai"></div>
          <div class="form-group col-md-2 mb-2"><input type="date" name="end_date" class="form-control form-control-sm" title="Selesai"></div>
          <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-outline-primary btn-block">+</button></div>
          <div class="form-group col-md-4 mb-2"><input type="number" name="last_salary" class="form-control form-control-sm" placeholder="Gaji akhir"></div>
          <div class="form-group col-md-8 mb-2"><input name="job_description" class="form-control form-control-sm" placeholder="Deskripsi singkat"></div>
        </form>
        @endunless
      </div>
    </div>

    {{-- Skill --}}
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Skill</div>
      <div class="card-body">
        @if($candidate->skills->isEmpty())
          <p class="text-muted small mb-0">Belum ada skill.</p>
        @else
          <div class="d-flex flex-wrap" style="gap:.4rem">
          @foreach($candidate->skills as $sk)
            <span class="badge badge-light border p-2">
              {{ $sk->name }}
              @if($sk->proficiency)<span class="text-muted">· {{ \App\Models\CandidateSkill::$proficiencyLabels[$sk->proficiency] ?? $sk->proficiency }}</span>@endif
              @unless($lockProfile)
              <form method="POST" action="{{ route('recruitment.candidates.profile.destroy', [$candidate, 'skill', $sk->id]) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                @csrf @method('DELETE')<button class="btn btn-link btn-sm p-0 ml-1 text-danger" style="line-height:1">&times;</button>
              </form>
              @endunless
            </span>
          @endforeach
          </div>
        @endif

        @unless($lockProfile)
        <form method="POST" action="{{ route('recruitment.candidates.profile.store', [$candidate, 'skill']) }}" class="form-row mt-3">
          @csrf
          <div class="form-group col-md-5 mb-2"><input name="name" class="form-control form-control-sm" placeholder="Skill *" required></div>
          <div class="form-group col-md-4 mb-2">
            <select name="proficiency" class="form-control form-control-sm">
              <option value="">-- Level --</option>
              @foreach(\App\Models\CandidateSkill::$proficiencyLabels as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select>
          </div>
          <div class="form-group col-md-3 mb-2"><button class="btn btn-sm btn-outline-primary btn-block">Tambah</button></div>
        </form>
        @endunless
      </div>
    </div>

    {{-- Sertifikasi --}}
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Sertifikasi</div>
      <div class="card-body">
        @forelse($candidate->certifications as $ct)
          <div class="d-flex justify-content-between align-items-start border-bottom py-2">
            <div>
              <div class="font-weight-bold small">{{ $ct->name }}</div>
              <div class="small text-muted">{{ $ct->issuer ?: '—' }}
                @if($ct->issued_date) · terbit {{ $ct->issued_date->format('m/Y') }}@endif
                @if($ct->expires_date) · s/d {{ $ct->expires_date->format('m/Y') }}@endif
                @if($ct->credential_id) · ID {{ $ct->credential_id }}@endif</div>
            </div>
            @unless($lockProfile)
            <form method="POST" action="{{ route('recruitment.candidates.profile.destroy', [$candidate, 'certification', $ct->id]) }}" onsubmit="return confirm('Hapus?')">
              @csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="gd-trash"></i></button>
            </form>
            @endunless
          </div>
        @empty
          <p class="text-muted small mb-0">Belum ada sertifikasi.</p>
        @endforelse

        @unless($lockProfile)
        <form method="POST" action="{{ route('recruitment.candidates.profile.store', [$candidate, 'certification']) }}" class="form-row mt-3">
          @csrf
          <div class="form-group col-md-4 mb-2"><input name="name" class="form-control form-control-sm" placeholder="Nama sertifikat *" required></div>
          <div class="form-group col-md-3 mb-2"><input name="issuer" class="form-control form-control-sm" placeholder="Penerbit"></div>
          <div class="form-group col-md-2 mb-2"><input type="date" name="issued_date" class="form-control form-control-sm" title="Terbit"></div>
          <div class="form-group col-md-2 mb-2"><input type="date" name="expires_date" class="form-control form-control-sm" title="Berlaku s/d"></div>
          <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-outline-primary btn-block">+</button></div>
        </form>
        @endunless
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
