@extends('layouts.grain')
                @section('title', 'Template Penilaian')
                
                @section('content')
                @include('components.notification')
                
                <div class="card mb-3 mb-md-4">
                <div class="card-body">
                <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Template Penilaian</li>
                </ol>
                </nav>
                
                <div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
                <div class="h3 mb-0">Template Penilaian</div>
                <a href="{{ route('appraisal.templates.create') }}" class="btn btn-primary">Tambah Template</a>
                </div>
                
                <div class="table-responsive-xl">
                <table id="dt-templates" class="table text-nowrap mb-0">
                <thead>
                <tr>
                <th class="font-weight-semi-bold border-top-0 py-2">#</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Nama Template</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Level</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Jml Aspek</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Default</th>
                <th class="font-weight-semi-bold border-top-0 py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($templates as $template)
                <tr>
                <td class="py-3">{{ $template->id }}</td>
                <td class="py-3 font-weight-bold">{{ $template->name }}</td>
                <td class="py-3">{{ $template->level->name ?? '-' }}</td>
                <td class="py-3">{{ $template->aspects_count }}</td>
                <td class="py-3">
                @if($template->is_default)
                <span class="badge badge-success">Default</span>
                @endif
                </td>
                <td class="py-3">
                <a href="{{ route('appraisal.templates.edit', $template) }}" class="btn btn-sm btn-outline-primary mr-1">
                <i class="gd-pencil icon-text"></i> Edit
                </a>
                <a href="#" class="btn btn-sm btn-outline-danger"
                data-confirm="Hapus template &ldquo;{{ $template->name }}&rdquo;? Semua aspek dan bobot di dalamnya akan ikut terhapus."
                data-confirm-title="Hapus Template"
                data-form="del-tmpl-{{ $template->id }}">
                <i class="gd-trash icon-text"></i>
                </a>
                <form id="del-tmpl-{{ $template->id }}" action="{{ route('appraisal.templates.destroy', $template) }}" method="POST" class="d-none">
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
                <script>$(function(){ $('#dt-templates').DataTable({ language: window.siproDtLang, order:[[0,'asc']], columnDefs:[{orderable:false,targets:-1}] }); });</script>
                @endsection
                