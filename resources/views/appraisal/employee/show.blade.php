@extends('layouts.grain')
@section('title', 'Profil ' . $employee->name)

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appraisal.employees.index') }}">Data Karyawan</a></li>
                <li class="breadcrumb-item active">Profil</li>
            </ol>
        </nav>

        <div class="d-flex align-items-center flex-wrap mb-4" style="gap:1.25rem">
            <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center"
                 style="width:72px;height:72px;background:#eef1f5;border:2px solid #e5e7eb;flex:0 0 auto">
                @if($employee->photo)
                    <img src="{{ route('appraisal.employees.photo', $employee) }}" style="width:100%;height:100%;object-fit:cover" alt="">
                @else
                    <i class="gd-user text-muted"></i>
                @endif
            </div>
            <div class="mr-auto">
                <h4 class="mb-0">{{ $employee->name }}</h4>
                <div class="text-muted small">{{ $employee->nip ?: '—' }} · {{ $employee->position?->name ?: '—' }}</div>
            </div>
            <div>
                <a href="{{ route('appraisal.employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary mr-1">Edit</a>
                <a href="{{ route('appraisal.employees.pdf', $employee) }}" class="btn btn-sm btn-primary">Unduh PDF</a>
            </div>
        </div>

        {{-- Tab bisa digeser ke kanan bila tidak muat --}}
        <ul class="nav nav-tabs flex-nowrap profile-tabs mb-3" role="tablist">
            @foreach($sections as $title => $rows)
                <li class="nav-item">
                    <a class="nav-link text-nowrap {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#tab-{{ \Illuminate\Support\Str::slug($title) }}" role="tab">{{ $title }}</a>
                </li>
            @endforeach
            <li class="nav-item"><a class="nav-link text-nowrap" data-toggle="tab" href="#tab-riwayat" role="tab">Riwayat Perubahan</a></li>
        </ul>

        <div class="tab-content">
        @foreach($sections as $title => $rows)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ \Illuminate\Support\Str::slug($title) }}" role="tabpanel">
                <dl class="row mb-0 border-top">
                    @foreach($rows as $label => $value)
                        <dt class="col-sm-4 col-md-3 font-weight-normal text-muted py-2 border-bottom">{{ $label }}</dt>
                        <dd class="col-sm-8 col-md-9 py-2 mb-0 border-bottom">{{ filled($value) ? $value : '—' }}</dd>
                    @endforeach
                </dl>
                @if($title === 'Jaminan' && ($employee->guarantee1_file || $employee->guarantee2_file))
                    <div class="mt-3 small">
                        @foreach([1, 2] as $slot)
                            @if($employee->{"guarantee{$slot}_file"})
                                <a href="{{ route('appraisal.employees.guarantee', [$employee, $slot]) }}" target="_blank" class="mr-3">Buka file Jaminan {{ $slot }}</a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        {{-- ── Riwayat perubahan (tab terakhir) ── --}}
        @php $cats = \App\Models\EmployeeHistory::CATEGORIES; @endphp
            <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
        <ul class="nav nav-pills mb-3 flex-wrap" role="tablist">
            @foreach($cats as $key => [$label])
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#hist-{{ $key }}" role="tab">
                        {{ $label }} @if(($histories[$key] ?? collect())->count())<span class="badge badge-primary">{{ $histories[$key]->count() }}</span>@endif
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            @foreach($cats as $key => [$label, $newLabel])
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="hist-{{ $key }}" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-primary-soft">
                                <tr><th style="width:130px">Tanggal Perubahan</th><th>Sebelumnya</th><th>{{ $newLabel }}</th><th>Catatan</th><th style="width:40px"></th></tr>
                            </thead>
                            <tbody>
                            @forelse($histories[$key] ?? [] as $h)
                                <tr>
                                    <td>{{ $h->effective_date->format('d/m/Y') }}</td>
                                    <td class="text-muted">{{ $h->old_value ?: '—' }}</td>
                                    <td class="font-weight-bold">{{ $h->new_value ?: '—' }}</td>
                                    <td class="small">{{ $h->note ?: '' }}</td>
                                    <td>
                                        @can('employee-master.edit')
                                        <form method="POST" action="{{ route('appraisal.employees.histories.destroy', [$employee, $h]) }}" onsubmit="return confirm('Hapus baris riwayat ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-link btn-sm p-0 text-danger" title="Hapus">&times;</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada riwayat.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @can('employee-master.edit')
                    <form method="POST" action="{{ route('appraisal.employees.histories.store', $employee) }}" class="form-row align-items-end mt-3">
                        @csrf
                        <input type="hidden" name="category" value="{{ $key }}">
                        <div class="form-group col-6 col-md-2 mb-2"><label class="small mb-1">Tanggal berlaku</label><input type="date" name="effective_date" value="{{ now()->format('Y-m-d') }}" class="form-control form-control-sm" required></div>
                        <div class="form-group col-6 col-md-3 mb-2"><label class="small mb-1">Sebelumnya</label><input type="text" name="old_value" class="form-control form-control-sm" maxlength="255"></div>
                        <div class="form-group col-6 col-md-3 mb-2"><label class="small mb-1">{{ $newLabel }}</label><input type="text" name="new_value" class="form-control form-control-sm" maxlength="255" required></div>
                        <div class="form-group col-6 col-md-2 mb-2"><label class="small mb-1">Catatan</label><input type="text" name="note" class="form-control form-control-sm" maxlength="255"></div>
                        <div class="form-group col-12 col-md-2 mb-2"><button class="btn btn-sm btn-primary btn-block">Tambah</button></div>
                    </form>
                    @endcan
                </div>
            @endforeach
        </div>
            </div>{{-- /pane Riwayat --}}
        </div>{{-- /tab-content utama --}}
    </div>
</div>
@endsection

@section('styles')
<style>
  .profile-tabs { overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; scrollbar-width: thin; }
  .profile-tabs .nav-link { white-space: nowrap; }
</style>
@endsection

@section('scripts')
<script>
  // Redirect dari tambah/hapus riwayat (#riwayat) langsung membuka tab Riwayat Perubahan.
  if (location.hash === '#riwayat') { $('.profile-tabs a[href="#tab-riwayat"]').tab('show'); }
</script>
@endsection
