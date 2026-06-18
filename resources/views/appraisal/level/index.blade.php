@extends('layouts.grain')
                @section('title', 'Level Jabatan')
                
                @section('content')
                @include('components.notification')
                
                <div class="card mb-3 mb-md-4">
                <div class="card-body">
                <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Level Jabatan</li>
                </ol>
                </nav>
                
                <div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
                <div class="h3 mb-0">Level Jabatan</div>
                <a href="{{ route('appraisal.levels.create') }}" class="btn btn-primary">Tambah Level</a>
                </div>
                
                <div class="table-responsive-xl">
                <table id="dt-levels" class="table text-nowrap mb-0">
                <thead>
                <tr>
                <th class="font-weight-semi-bold border-top-0 py-2">#</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Nama Level</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Deskripsi</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Jml Karyawan</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($levels as $level)
                <tr>
                <td class="py-3">{{ $level->id }}</td>
                <td class="py-3 font-weight-bold">{{ $level->name }}</td>
                <td class="py-3">{{ $level->description ?? '-' }}</td>
                <td class="py-3">{{ $level->employees_count }}</td>
                <td class="py-3">
                <a href="{{ route('appraisal.levels.edit', $level) }}" class="link-dark d-inline-block mr-2">
                <i class="gd-pencil icon-text"></i>
                </a>
                <a href="#" class="link-dark d-inline-block"
                data-confirm="Hapus level &ldquo;{{ $level->name }}&rdquo;? Level yang sedang dipakai karyawan tidak bisa dihapus."
                data-confirm-title="Hapus Level"
                data-form="del-level-{{ $level->id }}">
                <i class="gd-trash icon-text"></i>
                </a>
                <form id="del-level-{{ $level->id }}" action="{{ route('appraisal.levels.destroy', $level) }}" method="POST" class="d-none">
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
                <script>$(function(){ $('#dt-levels').DataTable({ language: window.siproDtLang, order: [[0,'asc']], columnDefs:[{orderable:false, targets:-1}] }); });</script>
                @endsection
                