@extends('layouts.grain')
                @section('title', 'Laporan Penilaian Kinerja')
                
                @section('content')
                @include('components.notification')
                
                <div class="card mb-3 mb-md-4">
                <div class="card-body">
                <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Laporan</li>
                </ol>
                </nav>
                
                <div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
                <div class="h3 mb-0">Laporan Penilaian Kinerja</div>
                <a href="{{ route('appraisal.report.export', request()->query()) }}"
                   class="btn btn-outline-success btn-sm" data-no-loading>
                    <i class="gd-download icon-text"></i> Export Excel
                </a>
                </div>
                
                {{-- Filter --}}
                <form method="GET" action="{{ route('appraisal.report.index') }}" class="mb-4">
                <div class="form-row align-items-end">
                <div class="form-group col-12 col-md-3 mb-2">
                <label class="mb-1">Periode</label>
                <select name="period_id" class="form-control form-control-sm">
                <option value="">Semua Periode</option>
                @foreach($periods as $p)
                <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>
                {{ $p->name }}
                </option>
                @endforeach
                </select>
                </div>
                <div class="form-group col-6 col-md-2 mb-2">
                <label class="mb-1">Departemen</label>
                <select name="department" class="form-control form-control-sm">
                <option value="">Semua</option>
                @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>
                {{ $dept }}
                </option>
                @endforeach
                </select>
                </div>
                <div class="form-group col-6 col-md-2 mb-2">
                <label class="mb-1">Status</label>
                <select name="status" class="form-control form-control-sm">
                <option value="">Semua</option>
                <option value="draft"          {{ request('status') === 'draft'          ? 'selected' : '' }}>Draft</option>
                <option value="submitted"      {{ request('status') === 'submitted'      ? 'selected' : '' }}>Menunggu User II</option>
                <option value="approved_user2" {{ request('status') === 'approved_user2' ? 'selected' : '' }}>Menunggu Final</option>
                <option value="approved_cfo"   {{ request('status') === 'approved_cfo'   ? 'selected' : '' }}>Final</option>
                <option value="rejected"       {{ request('status') === 'rejected'       ? 'selected' : '' }}>Dikembalikan</option>
                </select>
                </div>
                <div class="form-group col-6 col-md-2 mb-2">
                <label class="mb-1">Grade</label>
                <select name="grade" class="form-control form-control-sm">
                <option value="">Semua</option>
                @foreach($grades as $g)
                <option value="{{ $g }}" {{ request('grade') === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
                </select>
                </div>
                <div class="form-group col-auto mb-2">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['period_id','department','status','grade']))
                <a href="{{ route('appraisal.report.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
                @endif
                </div>
                </div>
                </form>
                
                {{-- Summary badges --}}
                @if($appraisals->count() > 0)
                <div class="mb-3">
                <span class="badge badge-light border mr-1">Total: <strong>{{ $appraisals->count() }}</strong></span>
                </div>
                @endif
                
                <div class="table-responsive-xl">
                <table id="dt-report" class="table mb-0">
                <thead>
                <tr>
                <th class="font-weight-semi-bold border-top-0 py-2">#</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Karyawan</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Dept</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Level</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Periode</th>
                <th class="font-weight-semi-bold border-top-0 py-2 text-center">Skor</th>
                <th class="font-weight-semi-bold border-top-0 py-2 text-center">Grade</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Status</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($appraisals as $a)
                @php
                $badgeClass = match($a->status) {
                'draft'          => 'secondary',
                'submitted'      => 'warning',
                'approved_user2' => 'info',
                'approved_cfo'   => 'success',
                'rejected'       => 'danger',
                default          => 'secondary',
                };
                @endphp
                <tr>
                <td class="py-2">{{ $loop->iteration }}</td>
                <td class="py-2">
                <div class="font-weight-bold">{{ $a->employee->name }}</div>
                <small class="text-muted">{{ $a->employee->nip }} · {{ $a->employee->position }}</small>
                </td>
                <td class="py-2">{{ $a->employee->department ?? '-' }}</td>
                <td class="py-2">{{ $a->employee->level?->name ?? '-' }}</td>
                <td class="py-2">{{ $a->period->name }}</td>
                <td class="py-2 text-center font-weight-bold">{{ $a->total_score ?: '-' }}</td>
                <td class="py-2 text-center">
                @if($a->grade)
                <span class="badge badge-info">{{ $a->grade }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
                </td>
                <td class="py-2">
                <span class="badge badge-{{ $badgeClass }}">{{ $a->status_label }}</span>
                </td>
                <td class="py-2">
                <a href="{{ route('appraisal.appraisals.show', $a) }}"
                class="btn btn-sm btn-outline-info mr-1" title="Detail">
                <i class="gd-eye icon-text"></i>
                </a>
                @if($a->status === 'approved_cfo')
                <a href="{{ route('appraisal.appraisals.pdf', $a) }}"
                class="btn btn-sm btn-outline-secondary" title="Download PDF" target="_blank">
                <i class="gd-download icon-text"></i>
                </a>
                @endif
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
                $(function(){
                $('#dt-report').DataTable({
                language: window.siproDtLang,
                order: [[0,'desc']],
                columnDefs: [{ orderable: false, targets: -1 }]
                });
                });
                </script>
                @endsection
                