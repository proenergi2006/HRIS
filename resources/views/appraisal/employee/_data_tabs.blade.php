@php
    // Partial ini di-include SETELAH form utama ditutup — jadi form-form CRUD
    // di sini berdiri sendiri (tidak nested). Butuh: $employee + master data
    // dari EmployeeController::formOptions().
    use App\Models\EmployeeFamilyMember;
    use App\Models\EmployeeSkill;
    use App\Models\EmployeeOrgExperience;
    use App\Models\EmployeeContract;

    $rp = fn (string $n, ...$a) => route("appraisal.employees.$n", [$employee, ...$a]);
@endphp

<ul class="nav nav-tabs mb-3" role="tablist" id="empDataTabs">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-family" role="tab">Keluarga</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-nssf" role="tab">BPJS</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-education" role="tab">Pendidikan</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-work-exp" role="tab">Pengalaman Kerja</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-skill" role="tab">Skill</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-org-exp" role="tab">Riwayat Organisasi</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-facility" role="tab">Fasilitas</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-bank" role="tab">Rekening Bank</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-contract" role="tab">Kontrak</a></li>
</ul>

<div class="tab-content">

    {{-- ═══ KELUARGA ═══ --}}
    <div class="tab-pane fade show active" id="tab-family" role="tabpanel">
        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.85rem">
            <i class="gd-info mr-1"></i> Maksimal <strong>1 istri/suami</strong> dan <strong>2 anak</strong> ditanggung medical reimbursement. Dipakai sebagai pilihan "Nama Pasien".
        </div>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th>Nama</th><th style="width:160px">Hubungan</th><th style="width:150px">Tgl Lahir</th><th style="width:90px"></th></tr></thead>
                <tbody>
                @forelse($employee->familyMembers as $fm)
                    @php $f = 'fam-form-' . $fm->id; @endphp
                    <tr>
                        <td><input type="text" name="name" form="{{ $f }}" value="{{ $fm->name }}" class="form-control form-control-sm" required maxlength="100"></td>
                        <td>
                            <select name="relation" form="{{ $f }}" class="form-control form-control-sm" required>
                                @foreach(EmployeeFamilyMember::$relationLabels as $val => $label)<option value="{{ $val }}" @selected($fm->relation === $val)>{{ $label }}</option>@endforeach
                            </select>
                        </td>
                        <td><input type="date" name="birth_date" form="{{ $f }}" value="{{ $fm->birth_date?->format('Y-m-d') }}" class="form-control form-control-sm" max="{{ now()->format('Y-m-d') }}"></td>
                        <td class="text-center" style="white-space:nowrap">
                            <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                            <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus {{ $fm->name }}?" data-confirm-title="Hapus Anggota Keluarga" data-form="fam-del-{{ $fm->id }}"><i class="gd-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @foreach($employee->familyMembers as $fm)
            <form id="fam-form-{{ $fm->id }}" method="POST" action="{{ $rp('family-members.update', $fm) }}" class="d-none">@csrf @method('PUT')</form>
            <form id="fam-del-{{ $fm->id }}" method="POST" action="{{ $rp('family-members.destroy', $fm) }}" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ $rp('family-members.store') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-4 mb-2"><label class="small font-weight-bold mb-1">Nama</label><input type="text" name="name" class="form-control form-control-sm" required maxlength="100"></div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Hubungan</label>
                <select name="relation" class="form-control form-control-sm" required>
                    @foreach(EmployeeFamilyMember::$relationLabels as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Tgl Lahir</label><input type="date" name="birth_date" class="form-control form-control-sm" max="{{ now()->format('Y-m-d') }}"></div>
            <div class="form-group col-md-2 mb-2"><button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus mr-1"></i>Tambah</button></div>
        </form>
    </div>

    {{-- ═══ BPJS / NSSF ═══ --}}
    <div class="tab-pane fade" id="tab-nssf" role="tabpanel">
        @php $nssf = $employee->nssf; @endphp
        <form method="POST" action="{{ $rp('nssf.update') }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-uppercase text-muted mb-2" style="font-size:.75rem">BPJS Kesehatan</h6>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="health_registered" name="health_registered" value="1" {{ optional($nssf)->health_registered ? 'checked' : '' }}>
                        <label class="custom-control-label" for="health_registered">Terdaftar</label>
                    </div>
                    <div class="form-group"><label class="small">No. BPJS Kesehatan</label><input type="text" name="health_number" class="form-control" value="{{ optional($nssf)->health_number }}"></div>
                    <div class="form-group"><label class="small">Tanggal Daftar</label><input type="date" name="health_join_date" class="form-control" value="{{ optional(optional($nssf)->health_join_date)->format('Y-m-d') }}"></div>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-uppercase text-muted mb-2" style="font-size:.75rem">BPJS Ketenagakerjaan</h6>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="employment_registered" name="employment_registered" value="1" {{ optional($nssf)->employment_registered ? 'checked' : '' }}>
                        <label class="custom-control-label" for="employment_registered">Terdaftar</label>
                    </div>
                    <div class="form-group"><label class="small">No. BPJS Ketenagakerjaan</label><input type="text" name="employment_number" class="form-control" value="{{ optional($nssf)->employment_number }}"></div>
                    <div class="form-group"><label class="small">Tanggal Daftar</label><input type="date" name="employment_join_date" class="form-control" value="{{ optional(optional($nssf)->employment_join_date)->format('Y-m-d') }}"></div>
                </div>
            </div>
            <button class="btn btn-primary btn-sm">Simpan Data BPJS</button>
        </form>
    </div>

    {{-- ═══ PENDIDIKAN ═══ --}}
    <div class="tab-pane fade" id="tab-education" role="tabpanel">
        @php $educations = $employee->educations()->with(['level','major'])->get(); @endphp
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th style="width:150px">Jenjang</th><th style="width:170px">Jurusan</th><th>Institusi</th><th style="width:100px">Lulus</th><th style="width:80px">IPK</th><th style="width:90px"></th></tr></thead>
                <tbody>
                @forelse($educations as $edu)
                    @php $f = 'edu-form-' . $edu->id; @endphp
                    <tr>
                        <td>
                            <select name="education_level_id" form="{{ $f }}" class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach($educationLevels as $l)<option value="{{ $l->id }}" @selected($edu->education_level_id === $l->id)>{{ $l->name }}</option>@endforeach
                            </select>
                        </td>
                        <td>
                            <select name="education_major_id" form="{{ $f }}" class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach($educationMajors as $m)<option value="{{ $m->id }}" @selected($edu->education_major_id === $m->id)>{{ $m->name }}</option>@endforeach
                            </select>
                        </td>
                        <td><input type="text" name="institution" form="{{ $f }}" value="{{ $edu->institution }}" class="form-control form-control-sm" maxlength="200"></td>
                        <td><input type="number" name="graduation_year" form="{{ $f }}" value="{{ $edu->graduation_year }}" class="form-control form-control-sm" min="1950" max="{{ now()->year + 1 }}"></td>
                        <td><input type="number" step="0.01" name="gpa" form="{{ $f }}" value="{{ $edu->gpa }}" class="form-control form-control-sm" min="0" max="4"></td>
                        <td class="text-center" style="white-space:nowrap">
                            <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                            <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus riwayat pendidikan ini?" data-confirm-title="Hapus Pendidikan" data-form="edu-del-{{ $edu->id }}"><i class="gd-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @foreach($educations as $edu)
            <form id="edu-form-{{ $edu->id }}" method="POST" action="{{ $rp('educations.update', $edu->id) }}" class="d-none">@csrf @method('PUT')</form>
            <form id="edu-del-{{ $edu->id }}" method="POST" action="{{ $rp('educations.destroy', $edu->id) }}" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ $rp('educations.store') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Jenjang</label>
                <select name="education_level_id" class="form-control form-control-sm"><option value="">—</option>@foreach($educationLevels as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select>
            </div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Jurusan</label>
                <select name="education_major_id" class="form-control form-control-sm"><option value="">—</option>@foreach($educationMajors as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select>
            </div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Institusi</label><input type="text" name="institution" class="form-control form-control-sm" maxlength="200"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Tahun Lulus</label><input type="number" name="graduation_year" class="form-control form-control-sm" min="1950" max="{{ now()->year + 1 }}"></div>
            <div class="form-group col-md-1 mb-2"><label class="small font-weight-bold mb-1">IPK</label><input type="number" step="0.01" name="gpa" class="form-control form-control-sm" min="0" max="4"></div>
            <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus"></i></button></div>
        </form>
    </div>

    {{-- ═══ PENGALAMAN KERJA ═══ --}}
    <div class="tab-pane fade" id="tab-work-exp" role="tabpanel">
        <p class="text-muted small">Riwayat kerja <strong>sebelum</strong> bergabung.</p>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th>Perusahaan</th><th style="width:130px">Kota</th><th style="width:130px">Mulai</th><th style="width:130px">Selesai</th><th>Jabatan Akhir</th><th style="width:90px"></th></tr></thead>
                <tbody>
                @forelse($employee->workExperiences as $we)
                    @php $f = 'we-form-' . $we->id; @endphp
                    <tr>
                        <td><input type="text" name="company_name" form="{{ $f }}" value="{{ $we->company_name }}" class="form-control form-control-sm" required maxlength="200"></td>
                        <td><input type="text" name="company_city" form="{{ $f }}" value="{{ $we->company_city }}" class="form-control form-control-sm"></td>
                        <td><input type="date" name="start_date" form="{{ $f }}" value="{{ $we->start_date?->format('Y-m-d') }}" class="form-control form-control-sm"></td>
                        <td><input type="date" name="end_date" form="{{ $f }}" value="{{ $we->end_date?->format('Y-m-d') }}" class="form-control form-control-sm"></td>
                        <td><input type="text" name="end_job_title" form="{{ $f }}" value="{{ $we->end_job_title }}" class="form-control form-control-sm"></td>
                        <td class="text-center" style="white-space:nowrap">
                            <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                            <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus pengalaman kerja ini?" data-confirm-title="Hapus Pengalaman Kerja" data-form="we-del-{{ $we->id }}"><i class="gd-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @foreach($employee->workExperiences as $we)
            <form id="we-form-{{ $we->id }}" method="POST" action="{{ $rp('work-experiences.update', $we->id) }}" class="d-none">
                @csrf @method('PUT')
                <input type="hidden" name="job_description" value="{{ $we->job_description }}">
                <input type="hidden" name="remarks" value="{{ $we->remarks }}">
            </form>
            <form id="we-del-{{ $we->id }}" method="POST" action="{{ $rp('work-experiences.destroy', $we->id) }}" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ $rp('work-experiences.store') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Nama Perusahaan</label><input type="text" name="company_name" class="form-control form-control-sm" required maxlength="200"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Kota</label><input type="text" name="company_city" class="form-control form-control-sm"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Mulai</label><input type="date" name="start_date" class="form-control form-control-sm"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Selesai</label><input type="date" name="end_date" class="form-control form-control-sm"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Jabatan Akhir</label><input type="text" name="end_job_title" class="form-control form-control-sm"></div>
            <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus"></i></button></div>
            <div class="form-group col-md-6 mb-2"><input type="text" name="job_description" class="form-control form-control-sm" placeholder="Uraian pekerjaan (opsional)"></div>
            <div class="form-group col-md-6 mb-2"><input type="text" name="remarks" class="form-control form-control-sm" placeholder="Catatan (opsional)"></div>
        </form>
    </div>

    {{-- ═══ SKILL ═══ --}}
    <div class="tab-pane fade" id="tab-skill" role="tabpanel">
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th>Skill</th><th style="width:150px">Level</th><th>Catatan</th><th style="width:90px"></th></tr></thead>
                <tbody>
                @forelse($employee->skills as $sk)
                    @php $f = 'sk-form-' . $sk->id; @endphp
                    <tr>
                        <td><input type="text" name="name" form="{{ $f }}" value="{{ $sk->name }}" class="form-control form-control-sm" required maxlength="150"></td>
                        <td>
                            <select name="proficiency" form="{{ $f }}" class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach(EmployeeSkill::$proficiencyLabels as $v => $l)<option value="{{ $v }}" @selected($sk->proficiency === $v)>{{ $l }}</option>@endforeach
                            </select>
                        </td>
                        <td><input type="text" name="notes" form="{{ $f }}" value="{{ $sk->notes }}" class="form-control form-control-sm"></td>
                        <td class="text-center" style="white-space:nowrap">
                            <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                            <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus skill {{ $sk->name }}?" data-confirm-title="Hapus Skill" data-form="sk-del-{{ $sk->id }}"><i class="gd-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @foreach($employee->skills as $sk)
            <form id="sk-form-{{ $sk->id }}" method="POST" action="{{ $rp('skills.update', $sk->id) }}" class="d-none">@csrf @method('PUT')</form>
            <form id="sk-del-{{ $sk->id }}" method="POST" action="{{ $rp('skills.destroy', $sk->id) }}" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ $rp('skills.store') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-4 mb-2"><label class="small font-weight-bold mb-1">Skill</label><input type="text" name="name" class="form-control form-control-sm" required maxlength="150"></div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Level</label>
                <select name="proficiency" class="form-control form-control-sm"><option value="">—</option>@foreach(EmployeeSkill::$proficiencyLabels as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group col-md-4 mb-2"><label class="small font-weight-bold mb-1">Catatan</label><input type="text" name="notes" class="form-control form-control-sm"></div>
            <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus"></i></button></div>
        </form>
    </div>

    {{-- ═══ RIWAYAT ORGANISASI ═══ --}}
    <div class="tab-pane fade" id="tab-org-exp" role="tabpanel">
        <p class="text-muted small">Histori jabatan/unit <strong>internal</strong> (promosi, rotasi, mutasi, pindah antar PT dalam grup). Sumber data Career Management.</p>
        @php $orgExps = $employee->orgExperiences()->orderBy('start_date')->get(); @endphp
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th style="width:130px">Jenis</th><th style="width:150px">Perusahaan</th><th>Unit</th><th>Jabatan</th><th style="width:120px">Mulai</th><th style="width:120px">Selesai</th><th style="width:90px"></th></tr></thead>
                <tbody>
                @forelse($orgExps as $oe)
                    @php $f = 'oe-form-' . $oe->id; @endphp
                    <tr>
                        <td>
                            <select name="change_type" form="{{ $f }}" class="form-control form-control-sm" required>
                                @foreach(EmployeeOrgExperience::$changeTypeLabels as $v => $l)<option value="{{ $v }}" @selected($oe->change_type === $v)>{{ $l }}</option>@endforeach
                            </select>
                        </td>
                        <td>
                            <select name="company_id" form="{{ $f }}" class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach($companies as $c)<option value="{{ $c->id }}" @selected($oe->company_id === $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
                            </select>
                        </td>
                        <td><input type="text" name="unit_name" form="{{ $f }}" value="{{ $oe->unit_name }}" class="form-control form-control-sm" placeholder="mis. Departemen IT"></td>
                        <td><input type="text" name="position_name" form="{{ $f }}" value="{{ $oe->position_name }}" class="form-control form-control-sm"></td>
                        <td><input type="date" name="start_date" form="{{ $f }}" value="{{ $oe->start_date?->format('Y-m-d') }}" class="form-control form-control-sm" required></td>
                        <td><input type="date" name="end_date" form="{{ $f }}" value="{{ $oe->end_date?->format('Y-m-d') }}" class="form-control form-control-sm"></td>
                        <td class="text-center" style="white-space:nowrap">
                            <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                            <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus riwayat organisasi ini?" data-confirm-title="Hapus Riwayat Organisasi" data-form="oe-del-{{ $oe->id }}"><i class="gd-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @foreach($orgExps as $oe)
            <form id="oe-form-{{ $oe->id }}" method="POST" action="{{ $rp('org-experiences.update', $oe->id) }}" class="d-none">
                @csrf @method('PUT')
                <input type="hidden" name="position_id" value="{{ $oe->position_id }}">
                <input type="hidden" name="remarks" value="{{ $oe->remarks }}">
            </form>
            <form id="oe-del-{{ $oe->id }}" method="POST" action="{{ $rp('org-experiences.destroy', $oe->id) }}" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ $rp('org-experiences.store') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Jenis</label>
                <select name="change_type" class="form-control form-control-sm" required>@foreach(EmployeeOrgExperience::$changeTypeLabels as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Perusahaan</label>
                <select name="company_id" class="form-control form-control-sm"><option value="">—</option>@foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach</select>
            </div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Unit</label><input type="text" name="unit_name" class="form-control form-control-sm" placeholder="mis. Departemen IT"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Jabatan</label><input type="text" name="position_name" class="form-control form-control-sm"></div>
            <div class="form-group col-md-1 mb-2"><label class="small font-weight-bold mb-1">Mulai</label><input type="date" name="start_date" class="form-control form-control-sm" required></div>
            <div class="form-group col-md-1 mb-2"><label class="small font-weight-bold mb-1">Selesai</label><input type="date" name="end_date" class="form-control form-control-sm"></div>
            <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus"></i></button></div>
        </form>
    </div>

    {{-- ═══ FASILITAS ═══ --}}
    <div class="tab-pane fade" id="tab-facility" role="tabpanel">
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th>Fasilitas</th><th>Keterangan</th><th style="width:140px">Diterima</th><th style="width:140px">Dikembalikan</th><th style="width:90px"></th></tr></thead>
                <tbody>
                @forelse($employee->facilities as $fac)
                    @php $f = 'fac-form-' . $fac->id; @endphp
                    <tr>
                        <td><input type="text" name="name" form="{{ $f }}" value="{{ $fac->name }}" class="form-control form-control-sm" required maxlength="150"></td>
                        <td><input type="text" name="description" form="{{ $f }}" value="{{ $fac->description }}" class="form-control form-control-sm"></td>
                        <td><input type="date" name="received_date" form="{{ $f }}" value="{{ $fac->received_date?->format('Y-m-d') }}" class="form-control form-control-sm"></td>
                        <td><input type="date" name="returned_date" form="{{ $f }}" value="{{ $fac->returned_date?->format('Y-m-d') }}" class="form-control form-control-sm"></td>
                        <td class="text-center" style="white-space:nowrap">
                            <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                            <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus fasilitas {{ $fac->name }}?" data-confirm-title="Hapus Fasilitas" data-form="fac-del-{{ $fac->id }}"><i class="gd-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @foreach($employee->facilities as $fac)
            <form id="fac-form-{{ $fac->id }}" method="POST" action="{{ $rp('facilities.update', $fac->id) }}" class="d-none">
                @csrf @method('PUT')
                <input type="hidden" name="remarks" value="{{ $fac->remarks }}">
            </form>
            <form id="fac-del-{{ $fac->id }}" method="POST" action="{{ $rp('facilities.destroy', $fac->id) }}" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ $rp('facilities.store') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Fasilitas</label><input type="text" name="name" class="form-control form-control-sm" required maxlength="150"></div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Keterangan</label><input type="text" name="description" class="form-control form-control-sm"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Diterima</label><input type="date" name="received_date" class="form-control form-control-sm"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Dikembalikan</label><input type="date" name="returned_date" class="form-control form-control-sm"></div>
            <div class="form-group col-md-2 mb-2"><button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus mr-1"></i>Tambah</button></div>
        </form>
    </div>

    {{-- ═══ REKENING BANK ═══ --}}
    <div class="tab-pane fade" id="tab-bank" role="tabpanel">
        @php $bankAccounts = $employee->bankAccounts()->with('bank')->get(); @endphp
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th style="width:170px">Bank</th><th style="width:160px">No. Rekening</th><th>Atas Nama</th><th style="width:80px">Utama</th><th style="width:90px"></th></tr></thead>
                <tbody>
                @forelse($bankAccounts as $ba)
                    @php $f = 'ba-form-' . $ba->id; @endphp
                    <tr>
                        <td>
                            <select name="bank_id" form="{{ $f }}" class="form-control form-control-sm" required>
                                <option value="">—</option>
                                @foreach($banks as $b)<option value="{{ $b->id }}" @selected($ba->bank_id === $b->id)>{{ $b->name }}</option>@endforeach
                            </select>
                        </td>
                        <td><input type="text" name="account_number" form="{{ $f }}" value="{{ $ba->account_number }}" class="form-control form-control-sm" required maxlength="50"></td>
                        <td><input type="text" name="account_holder_name" form="{{ $f }}" value="{{ $ba->account_holder_name }}" class="form-control form-control-sm" required maxlength="150"></td>
                        <td class="text-center align-middle">
                            <input type="hidden" name="is_primary" form="{{ $f }}" value="0">
                            <input type="checkbox" name="is_primary" form="{{ $f }}" value="1" {{ $ba->is_primary ? 'checked' : '' }}>
                        </td>
                        <td class="text-center" style="white-space:nowrap">
                            <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                            <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus rekening ini?" data-confirm-title="Hapus Rekening" data-form="ba-del-{{ $ba->id }}"><i class="gd-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @foreach($bankAccounts as $ba)
            <form id="ba-form-{{ $ba->id }}" method="POST" action="{{ $rp('bank-accounts.update', $ba->id) }}" class="d-none">
                @csrf @method('PUT')
                <input type="hidden" name="branch_name" value="{{ $ba->branch_name }}">
                <input type="hidden" name="is_active" value="1">
            </form>
            <form id="ba-del-{{ $ba->id }}" method="POST" action="{{ $rp('bank-accounts.destroy', $ba->id) }}" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ $rp('bank-accounts.store') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Bank</label>
                <select name="bank_id" class="form-control form-control-sm" required><option value="">—</option>@foreach($banks as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select>
            </div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">No. Rekening</label><input type="text" name="account_number" class="form-control form-control-sm" required maxlength="50"></div>
            <div class="form-group col-md-3 mb-2"><label class="small font-weight-bold mb-1">Atas Nama</label><input type="text" name="account_holder_name" class="form-control form-control-sm" required maxlength="150"></div>
            <div class="form-group col-md-2 mb-2"><div class="custom-control custom-checkbox mt-4"><input type="checkbox" class="custom-control-input" id="ba-primary" name="is_primary" value="1"><label class="custom-control-label small" for="ba-primary">Rekening utama</label></div></div>
            <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus"></i></button></div>
        </form>
    </div>

    {{-- ═══ KONTRAK ═══ --}}
    <div class="tab-pane fade" id="tab-contract" role="tabpanel">
        @php $contracts = $employee->contracts()->orderByDesc('start_date')->get(); @endphp
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th style="width:140px">Tipe</th><th style="width:140px">Nomor</th><th style="width:130px">Mulai</th><th style="width:130px">Berakhir</th><th style="width:130px">Status</th><th style="width:60px">Dok</th><th style="width:90px"></th></tr></thead>
                <tbody>
                @forelse($contracts as $ct)
                    @php $f = 'ct-form-' . $ct->id; @endphp
                    <tr>
                        <td>
                            <select name="contract_type" form="{{ $f }}" class="form-control form-control-sm" required>
                                @foreach(EmployeeContract::$typeLabels as $v => $l)<option value="{{ $v }}" @selected($ct->contract_type === $v)>{{ $l }}</option>@endforeach
                            </select>
                        </td>
                        <td><input type="text" name="number" form="{{ $f }}" value="{{ $ct->number }}" class="form-control form-control-sm"></td>
                        <td><input type="date" name="start_date" form="{{ $f }}" value="{{ $ct->start_date?->format('Y-m-d') }}" class="form-control form-control-sm" required></td>
                        <td><input type="date" name="end_date" form="{{ $f }}" value="{{ $ct->end_date?->format('Y-m-d') }}" class="form-control form-control-sm"></td>
                        <td>
                            <select name="status" form="{{ $f }}" class="form-control form-control-sm" required>
                                @foreach(EmployeeContract::$statusLabels as $v => $l)<option value="{{ $v }}" @selected($ct->status === $v)>{{ $l }}</option>@endforeach
                            </select>
                        </td>
                        <td class="text-center">@if($ct->document_path)<a href="{{ $rp('contracts.download', $ct) }}" class="btn btn-xs btn-outline-info"><i class="gd-download"></i></a>@endif</td>
                        <td class="text-center" style="white-space:nowrap">
                            <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                            <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus kontrak ini?" data-confirm-title="Hapus Kontrak" data-form="ct-del-{{ $ct->id }}"><i class="gd-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="small text-muted">Untuk ganti dokumen kontrak, hapus lalu tambah ulang dengan file baru (form edit di atas tidak termasuk upload file).</p>
        @foreach($contracts as $ct)
            <form id="ct-form-{{ $ct->id }}" method="POST" action="{{ $rp('contracts.update', $ct) }}" class="d-none">
                @csrf @method('PUT')
                <input type="hidden" name="notes" value="{{ $ct->notes }}">
            </form>
            <form id="ct-del-{{ $ct->id }}" method="POST" action="{{ $rp('contracts.destroy', $ct) }}" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ $rp('contracts.store') }}" class="form-row align-items-end" enctype="multipart/form-data">
            @csrf
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Tipe</label>
                <select name="contract_type" class="form-control form-control-sm" required>@foreach(EmployeeContract::$typeLabels as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Nomor</label><input type="text" name="number" class="form-control form-control-sm"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Mulai</label><input type="date" name="start_date" class="form-control form-control-sm" required></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Berakhir</label><input type="date" name="end_date" class="form-control form-control-sm"></div>
            <div class="form-group col-md-2 mb-2"><label class="small font-weight-bold mb-1">Status</label>
                <select name="status" class="form-control form-control-sm" required>@foreach(EmployeeContract::$statusLabels as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group col-md-2 mb-2"><button class="btn btn-sm btn-outline-primary btn-block"><i class="gd-plus mr-1"></i>Tambah</button></div>
            <div class="form-group col-md-6 mb-2"><label class="small font-weight-bold mb-1">Dokumen (PDF/gambar, opsional)</label><input type="file" name="document" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png"></div>
        </form>
    </div>

</div>
