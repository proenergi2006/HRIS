@extends('layouts.grain')
                @section('title', 'Data Penilaian')
                
                @section('content')
                @include('components.notification')
                
                <div class="card mb-3 mb-md-4">
                <div class="card-body">
                <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Data Penilaian</li>
                </ol>
                </nav>
                
                <div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
                <div class="h3 mb-0">Data Penilaian Kinerja</div>
                <a href="{{ route('appraisal.appraisals.create') }}" class="btn btn-primary">Buat Penilaian</a>
                </div>
                
                {{-- Filter --}}
                <form method="GET" action="{{ route('appraisal.appraisals.index') }}" class="mb-3">
                <div class="form-row align-items-end">
                <div class="form-group col-12 col-md-4 mb-2">
                <label for="period_id" class="mb-1">Filter Periode</label>
                <select name="period_id" id="period_id" class="form-control form-control-sm">
                <option value="">Semua Periode</option>
                @foreach($periods as $p)
                <option value="{{ $p->id }}" {{ $selectedPeriod == $p->id ? 'selected' : '' }}>
                {{ $p->name }}
                </option>
                @endforeach
                </select>
                </div>
                <div class="form-group col-auto mb-2">
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                @if($selectedPeriod)
                <a href="{{ route('appraisal.appraisals.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
                @endif
                </div>
                </div>
                </form>
                
                <div class="table-responsive-xl">
                <table id="dt-appraisals" class="table mb-0">
                <thead>
                <tr>
                <th class="font-weight-semi-bold border-top-0 py-2">#</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Karyawan</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Periode</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Template</th>
                <th class="font-weight-semi-bold border-top-0 py-2 text-center">Skor</th>
                <th class="font-weight-semi-bold border-top-0 py-2 text-center">Grade</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Status</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($appraisals as $appraisal)
                <tr>
                <td class="py-3">{{ $loop->iteration }}</td>
                <td class="py-3">
                <div class="font-weight-bold">{{ $appraisal->employee->name }}</div>
                <small class="text-muted">{{ $appraisal->employee->position }}</small>
                </td>
                <td class="py-3">{{ $appraisal->period->name }}</td>
                <td class="py-3">{{ $appraisal->template->name }}</td>
                <td class="py-3 text-center">{{ $appraisal->total_score ?: '-' }}</td>
                <td class="py-3 text-center">
                @if($appraisal->grade)
                <span class="badge badge-info">{{ $appraisal->grade }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
                </td>
                <td class="py-3">
                @php
                $badgeClass = match($appraisal->status) {
                'draft'          => 'secondary',
                'submitted'      => 'warning',
                'approved_user2' => 'info',
                'approved_cfo'   => 'success',
                'rejected'       => 'danger',
                default          => 'secondary',
                };
                @endphp
                <span class="badge badge-{{ $badgeClass }}">{{ $appraisal->status_label }}</span>
                </td>
                <td class="py-3">
                <a href="{{ route('appraisal.appraisals.show', $appraisal) }}"
                class="btn btn-sm btn-outline-info mr-1" title="Lihat detail">
                <i class="gd-eye icon-text"></i>
                </a>
                @if($appraisal->isDraft())
                <a href="{{ route('appraisal.appraisals.edit', $appraisal) }}"
                class="btn btn-sm btn-outline-primary mr-1" title="Edit">
                <i class="gd-pencil icon-text"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger"
                title="Hapus"
                data-confirm="Hapus penilaian &ldquo;{{ $appraisal->employee->name }}&rdquo; — {{ $appraisal->period->name }}? Tindakan ini tidak dapat dibatalkan."
                data-confirm-title="Hapus Penilaian"
                data-form="del-{{ $appraisal->id }}">
                <i class="gd-trash icon-text"></i>
                </button>
                <form id="del-{{ $appraisal->id }}"
                action="{{ route('appraisal.appraisals.destroy', $appraisal) }}"
                method="POST" class="d-none">
                @csrf @method('DELETE')
                </form>
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
                $('#dt-appraisals').DataTable({
                language: window.siproDtLang,
                order: [[0,'desc']],
                columnDefs: [{ orderable: false, targets: -1 }]
                });
                });
                </script>
                @endsection
                