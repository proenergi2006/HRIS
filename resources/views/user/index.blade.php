@extends('layouts.grain')
                @section('title', 'Users')
                
                @section('content')
                @include('components.notification')
                
                <div class="card mb-3 mb-md-4">
                <div class="card-body">
                <nav class="d-none d-md-block" aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Users</li>
                </ol>
                </nav>
                
                <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="h3 mb-0">Manajemen User</div>
                <a href="{{ route('user.create') }}" class="btn btn-primary">
                <i class="gd-plus mr-1"></i> Tambah User
                </a>
                </div>
                
                <div class="table-responsive">
                <table id="dt-users" class="table mb-0">
                <thead class="thead-light">
                <tr>
                <th class="py-2">Nama</th>
                <th class="py-2">Email</th>
                <th class="py-2">Role</th>
                <th class="py-2">Departemen</th>
                <th class="py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                <tr>
                <td class="align-middle py-2">
                <div class="d-flex align-items-center">
                <span class="avatar-placeholder mr-2"
                style="width:32px;height:32px;font-size:0.8rem;flex-shrink:0;">
                {{ substr($u->name, 0, 1) }}
                </span>
                <span>{{ $u->name }}</span>
                </div>
                </td>
                <td class="align-middle py-2 text-muted" style="font-size:0.88rem;">
                {{ $u->email }}
                </td>
                <td class="align-middle py-2">
                @foreach($u->roles as $role)
                <span class="badge badge-{{ match($role->name) {
                'admin'     => 'primary',
                'evaluator' => 'info',
                'user_ii'   => 'warning',
                'cfo'       => 'success',
                'ceo'       => 'success',
                default     => 'secondary',
                } }}">
                {{ match($role->name) {
                'admin'     => 'Admin HRD',
                'evaluator' => 'Evaluator',
                'user_ii'   => 'Approver Step 1',
                'cfo'       => 'CFO',
                'ceo'       => 'CEO',
                default     => $role->name,
                } }}
                </span>
                @endforeach
                @if($u->roles->isEmpty())
                <span class="text-muted" style="font-size:0.8rem;">—</span>
                @endif
                </td>
                <td class="align-middle py-2">
                {{ $u->department ?? '—' }}
                </td>
                <td class="align-middle py-2">
                <a href="{{ route('user.edit', $u) }}" class="btn btn-sm btn-outline-secondary mr-1">
                <i class="gd-pencil"></i> Edit
                </a>
                @if($u->id !== auth()->id())
                <button class="btn btn-sm btn-outline-danger"
                data-confirm="Hapus user &ldquo;{{ $u->name }}&rdquo;? User ini tidak akan bisa login lagi."
                data-confirm-title="Hapus User"
                data-form="del-{{ $u->id }}">
                <i class="gd-trash"></i>
                </button>
                <form id="del-{{ $u->id }}" action="{{ route('user.destroy', $u) }}"
                method="POST" style="display:none;">
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
                <script>$(function(){ $('#dt-users').DataTable({ language: window.siproDtLang, order:[[0,'asc']], columnDefs:[{orderable:false,targets:-1}] }); });</script>
                @endsection
                