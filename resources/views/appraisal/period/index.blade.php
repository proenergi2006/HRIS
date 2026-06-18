@extends('layouts.grain')
                @section('title', 'Periode Penilaian')
                
                @section('content')
                @include('components.notification')
                
                <div class="card mb-3 mb-md-4">
                <div class="card-body">
                <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Periode Penilaian</li>
                </ol>
                </nav>
                
                <div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
                <div class="h3 mb-0">Periode Penilaian</div>
                <a href="{{ route('appraisal.periods.create') }}" class="btn btn-primary">Tambah Periode</a>
                </div>
                
                <div class="table-responsive-xl">
                <table id="dt-periods" class="table mb-0">
                <thead>
                <tr>
                <th class="font-weight-semi-bold border-top-0 py-2">Nama Periode</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Tahun</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Mulai</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Selesai</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Jml Penilaian</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Status</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($periods as $period)
                <tr>
                <td class="py-3 font-weight-bold">{{ $period->name }}</td>
                <td class="py-3">{{ $period->year }}</td>
                <td class="py-3">{{ $period->start_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="py-3">{{ $period->end_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="py-3">{{ $period->appraisals_count }}</td>
                <td class="py-3">
                @if($period->status === 'open')
                <span class="badge badge-success">Buka</span>
                @else
                <span class="badge badge-secondary">Tutup</span>
                @endif
                </td>
                <td class="py-3">
                {{-- Toggle open/closed --}}
                <form action="{{ route('appraisal.periods.toggle', $period) }}" method="POST" class="d-inline">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm {{ $period->status === 'open' ? 'btn-outline-secondary' : 'btn-outline-success' }} mr-1"
                title="{{ $period->status === 'open' ? 'Tutup periode' : 'Buka periode' }}">
                {{ $period->status === 'open' ? 'Tutup' : 'Buka' }}
                </button>
                </form>
                <a href="{{ route('appraisal.periods.edit', $period) }}" class="btn btn-sm btn-outline-primary mr-1">
                <i class="gd-pencil icon-text"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger"
                data-confirm="Hapus periode &ldquo;{{ $period->name }}&rdquo;? Semua data penilaian di periode ini akan ikut terhapus."
                data-confirm-title="Hapus Periode"
                data-form="del-period-{{ $period->id }}">
                <i class="gd-trash icon-text"></i>
                </button>
                <form id="del-period-{{ $period->id }}" action="{{ route('appraisal.periods.destroy', $period) }}" method="POST" class="d-none">
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
                <script>$(function(){ $('#dt-periods').DataTable({ language: window.siproDtLang, order:[[1,'desc']], columnDefs:[{orderable:false,targets:-1}] }); });</script>
                @endsection
                