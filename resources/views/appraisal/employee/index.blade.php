@extends('layouts.grain')
                @section('title', 'Data Karyawan')
                
                @section('content')
                @include('components.notification')
                
                <div class="card mb-3 mb-md-4">
                <div class="card-body">
                <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Data Karyawan</li>
                </ol>
                </nav>
                
                <div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
                <div class="h3 mb-0">Data Karyawan</div>
                <div class="d-flex flex-wrap" style="gap:.5rem">
                  <a href="{{ route('appraisal.departments.index') }}" class="btn btn-outline-secondary">
                    <i class="gd-layers mr-1"></i> Departemen
                  </a>
                  <a href="{{ route('appraisal.positions.index') }}" class="btn btn-outline-secondary">
                    <i class="gd-layers mr-1"></i> Jabatan
                  </a>
                  <a href="{{ route('appraisal.employees.import.form') }}" class="btn btn-outline-primary">
                    <i class="gd-upload mr-1"></i> Import Excel
                  </a>
                  <a href="{{ route('laporan.export.karyawan') }}" class="btn btn-outline-success">
                    <i class="gd-download mr-1"></i> Export Excel
                  </a>
                  <a href="{{ route('appraisal.employees.create') }}" class="btn btn-primary">Tambah Karyawan</a>
                </div>
                </div>

                <form method="GET" class="form-row align-items-end mb-3">
                  <div class="form-group col-auto mb-0">
                    <label class="small font-weight-bold">Filter Perusahaan</label>
                    <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
                      <option value="">Semua Perusahaan</option>
                      @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>
                          {{ $c->short_name ?? $c->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  @if(request('company_id'))
                  <div class="form-group col-auto mb-0 ml-2">
                    <a href="{{ route('appraisal.employees.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                  </div>
                  @endif
                </form>

                <div class="table-responsive-xl">
                <table id="dt-employees" class="table mb-0">
                <thead>
                <tr>
                <th class="font-weight-semi-bold border-top-0 py-2"></th>
                <th class="font-weight-semi-bold border-top-0 py-2">#</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Nama</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Perusahaan</th>
                <th class="font-weight-semi-bold border-top-0 py-2">NIP</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Dept / LOB</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Jabatan</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Level</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Status</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Kontrak Berakhir</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($employees as $employee)
                <tr>
                <td class="py-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center overflow-hidden"
                       style="width:36px;height:36px;background:#eef1f5">
                    @if($employee->photo)
                      <img src="{{ route('appraisal.employees.photo', $employee) }}" style="width:100%;height:100%;object-fit:cover">
                    @else
                      <i class="gd-user" style="font-size:16px;color:#9ca3af"></i>
                    @endif
                  </div>
                </td>
                <td class="py-3">{{ $loop->iteration }}</td>
                <td class="py-3 font-weight-bold">{{ $employee->name }}</td>
                <td class="py-3">
                  @if($employee->company)
                    <span class="badge badge-secondary" style="font-size:.75rem">{{ $employee->company->short_name ?? $employee->company->name }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="py-3">{{ $employee->nip ?? '-' }}</td>
                <td class="py-3">
                {{ $employee->department->name ?? '-' }}
                @if($employee->lob) <small class="text-muted">/ {{ $employee->lob }}</small>@endif
                </td>
                <td class="py-3">{{ $employee->position->name ?? '-' }}</td>
                <td class="py-3">
                @if($employee->level)
                <span class="badge badge-info">{{ $employee->level->name }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
                </td>
                <td class="py-3">
                @if($employee->is_active)
                <span class="badge badge-success">Aktif</span>
                @else
                <span class="badge badge-secondary">Nonaktif</span>
                @endif
                <br><small class="text-muted">{{ $employee->employment_status_label }}</small>
                </td>
                <td class="py-3">
                @if($employee->employment_status === 'contract' && $employee->contract_end_date)
                    @php $sisa = now()->diffInDays($employee->contract_end_date, false); @endphp
                    @if($sisa < 0)
                        <span class="badge badge-danger">Berakhir {{ $employee->contract_end_date->format('d/m/Y') }}</span>
                    @elseif($sisa <= 14)
                        <span class="badge badge-danger">{{ $employee->contract_end_date->format('d/m/Y') }}</span>
                        <br><small class="text-danger font-weight-bold">{{ $sisa }} hari lagi</small>
                    @elseif($sisa <= 60)
                        <span class="badge badge-warning">{{ $employee->contract_end_date->format('d/m/Y') }}</span>
                        <br><small class="text-warning">{{ $sisa }} hari lagi</small>
                    @else
                        <span class="text-muted">{{ $employee->contract_end_date->format('d/m/Y') }}</span>
                    @endif
                @else
                    <span class="text-muted">—</span>
                @endif
                </td>
                <td class="py-3">
                <a href="{{ route('appraisal.employees.edit', $employee) }}" class="link-dark d-inline-block mr-2" title="Edit">
                <i class="gd-pencil icon-text"></i>
                </a>
                <a href="{{ route('appraisal.employees.documents.index', $employee) }}" class="link-dark d-inline-block mr-2" title="Dokumen">
                <i class="gd-file icon-text"></i>
                </a>
                <a href="#" class="link-dark d-inline-block"
                data-confirm="Hapus karyawan &ldquo;{{ $employee->name }}&rdquo;? Data yang terkait juga akan ikut terhapus."
                data-confirm-title="Hapus Karyawan"
                data-form="del-emp-{{ $employee->id }}">
                <i class="gd-trash icon-text"></i>
                </a>
                <form id="del-emp-{{ $employee->id }}" action="{{ route('appraisal.employees.destroy', $employee) }}" method="POST" class="d-none">
                @csrf @method('DELETE')
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
                <script>$(function(){ $('#dt-employees').DataTable({ language: window.siproDtLang, order:[[2,'asc']], columnDefs:[{orderable:false,targets:[0,3,-1]}] }); });</script>
                @endsection
                