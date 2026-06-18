@extends('layouts.grain')
                @section('title', 'Konfigurasi Alur Approval')
                
                @section('content')
                @include('components.notification')
                
                <div class="card mb-3 mb-md-4">
                <div class="card-body">
                <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Alur Approval</li>
                </ol>
                </nav>
                
                <div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
                <div>
                <div class="h3 mb-0">Konfigurasi Alur Approval</div>
                <small class="text-muted">
                Tentukan siapa yang menyetujui penilaian per departemen.
                Baris tanpa departemen = default untuk semua departemen yang tidak dikonfigurasi.
                </small>
                </div>
                <a href="{{ route('appraisal.flow-configs.create') }}" class="btn btn-primary">Tambah Konfigurasi</a>
                </div>
                
                <div class="table-responsive-xl">
                <table id="dt-flowconfig" class="table mb-0">
                <thead>
                <tr>
                <th class="font-weight-semi-bold border-top-0 py-2">Departemen</th>
                <th class="font-weight-semi-bold border-top-0 py-2 text-center">Step</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Role (Approver)</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Label Tampil</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($configs as $config)
                <tr>
                <td class="py-3">
                @if($config->department)
                <span class="font-weight-bold">{{ $config->department }}</span>
                @else
                <span class="badge badge-secondary">Default</span>
                <small class="text-muted ml-1">(semua departemen)</small>
                @endif
                </td>
                <td class="py-3 text-center">
                <span class="badge badge-{{ $config->step === 1 ? 'info' : 'primary' }}">
                Step {{ $config->step }}
                </span>
                </td>
                <td class="py-3"><code>{{ $config->role }}</code></td>
                <td class="py-3">{{ $config->label }}</td>
                <td class="py-3">
                <a href="{{ route('appraisal.flow-configs.edit', $config) }}"
                class="btn btn-sm btn-outline-primary mr-1">
                <i class="gd-pencil icon-text"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger"
                data-confirm="Hapus konfigurasi alur untuk &ldquo;{{ $config->department ?? 'Default' }}&rdquo; Step {{ $config->step }}?"
                data-confirm-title="Hapus Konfigurasi"
                data-form="del-fc-{{ $config->id }}">
                <i class="gd-trash icon-text"></i>
                </button>
                <form id="del-fc-{{ $config->id }}"
                action="{{ route('appraisal.flow-configs.destroy', $config) }}"
                method="POST" class="d-none">
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
                <script>$(function(){ $('#dt-flowconfig').DataTable({ language: window.siproDtLang, order:[[0,'asc'],[1,'asc']], columnDefs:[{orderable:false,targets:-1}] }); });</script>
                @endsection
                