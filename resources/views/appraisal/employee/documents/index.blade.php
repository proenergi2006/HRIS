@extends('layouts.grain')
@section('title', 'Dokumen Karyawan — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <div class="h4 mb-0">Employee Digital File</div>
    <small class="text-muted">
      <a href="{{ route('appraisal.employees.index') }}">Karyawan</a> /
      <a href="{{ route('appraisal.employees.edit', $employee) }}">{{ $employee->name }}</a> /
      Dokumen
    </small>
  </div>
  <a href="{{ route('appraisal.employees.documents.create', $employee) }}" class="btn btn-primary btn-sm">
    <i class="gd-plus icon-text"></i> Unggah Dokumen
  </a>
</div>

@php
  $expired = $documents->filter(fn ($d) => $d->isExpired());
  $expiring = $documents->filter(fn ($d) => $d->isExpiringSoon());
@endphp
@if($expired->isNotEmpty() || $expiring->isNotEmpty())
  <div class="alert {{ $expired->isNotEmpty() ? 'alert-danger' : 'alert-warning' }} py-2 px-3" style="font-size:.85rem">
    <i class="gd-alert mr-1"></i>
    @if($expired->isNotEmpty())<strong>{{ $expired->count() }} dokumen sudah kadaluarsa</strong>@endif
    @if($expired->isNotEmpty() && $expiring->isNotEmpty()) · @endif
    @if($expiring->isNotEmpty())<strong>{{ $expiring->count() }} akan kadaluarsa ≤30 hari</strong>@endif
    — HR juga menerima email pengingat otomatis (job harian).
  </div>
@endif

@if($documents->isEmpty() && $movementLetters->isEmpty())
  <div class="card">
    <div class="card-body text-center py-5 text-muted">
      Belum ada dokumen. Klik <strong>Unggah Dokumen</strong> untuk menambahkan.
    </div>
  </div>
@else

@foreach(\App\Models\EmployeeDocument::$groupLabels as $groupKey => $groupLabel)
  @php
    $groupDocs = $documents->where('group', $groupKey);
    $showMovementLetters = $groupKey === 'movement' && $movementLetters->isNotEmpty();
  @endphp
  @continue($groupDocs->isEmpty() && ! $showMovementLetters)
  <div class="card mb-3">
    <div class="card-header font-weight-bold">{{ $groupLabel }} <span class="text-muted small">({{ $groupDocs->count() + ($showMovementLetters ? $movementLetters->count() : 0) }})</span></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Jenis</th><th>Judul</th><th>File</th><th>Kadaluarsa</th><th>Diunggah</th><th></th></tr>
          </thead>
          <tbody>
          @foreach($groupDocs as $doc)
            <tr>
              <td><span class="badge badge-secondary">{{ \App\Models\EmployeeDocument::$docTypes[$doc->doc_type] ?? $doc->doc_type }}</span></td>
              <td>{{ $doc->title }}</td>
              <td>
                <a href="{{ route('appraisal.employees.documents.download', [$employee, $doc]) }}" class="btn btn-xs btn-outline-primary">
                  <i class="gd-download icon-text"></i> Unduh
                </a>
                <small class="text-muted d-block" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $doc->original_name }}</small>
              </td>
              <td>
                @if($doc->expires_at)
                  @if($doc->isExpired())<span class="badge badge-danger">Kadaluarsa<br>{{ $doc->expires_at->format('d M Y') }}</span>
                  @elseif($doc->isExpiringSoon())<span class="badge badge-warning">Segera<br>{{ $doc->expires_at->format('d M Y') }}</span>
                  @else<span class="text-muted">{{ $doc->expires_at->format('d M Y') }}</span>@endif
                @else<span class="text-muted">—</span>@endif
              </td>
              <td><small class="text-muted">{{ $doc->created_at->format('d/m/y') }}</small></td>
              <td>
                <form method="POST" action="{{ route('appraisal.employees.documents.destroy', [$employee, $doc]) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-xs btn-outline-danger"><i class="gd-trash icon-text"></i></button>
                </form>
              </td>
            </tr>
          @endforeach
          @if($showMovementLetters)
            @foreach($movementLetters as $lt)
              <tr class="table-light">
                <td><span class="badge badge-info">{{ $lt->categoryLabel() }}</span></td>
                <td>{{ $lt->title }} <span class="text-muted small">#{{ $lt->letter_number }}</span></td>
                <td><a href="{{ route('appraisal.employee-letters.pdf', $lt) }}" target="_blank" class="btn btn-xs btn-outline-info"><i class="gd-file icon-text"></i> Lihat PDF</a>
                  <small class="text-muted d-block">dari Manajemen Surat</small></td>
                <td class="text-muted">—</td>
                <td><small class="text-muted">{{ $lt->issued_date?->format('d/m/y') }}</small></td>
                <td></td>
              </tr>
            @endforeach
          @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endforeach
@endif
@endsection
