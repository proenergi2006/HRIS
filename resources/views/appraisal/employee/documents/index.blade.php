@extends('layouts.grain')
@section('title', 'Dokumen Karyawan — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <div class="h4 mb-0">Dokumen Karyawan</div>
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

@if($documents->isEmpty())
  <div class="card">
    <div class="card-body text-center py-5 text-muted">
      Belum ada dokumen. Klik <strong>Unggah Dokumen</strong> untuk menambahkan.
    </div>
  </div>
@else
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Jenis</th>
            <th>Judul</th>
            <th>File</th>
            <th>Kadaluarsa</th>
            <th>Keterangan</th>
            <th>Diunggah</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($documents as $doc)
          <tr>
            <td>
              <span class="badge badge-secondary">{{ $doc->doc_type }}</span>
            </td>
            <td>{{ $doc->title }}</td>
            <td>
              <a href="{{ route('appraisal.employees.documents.download', [$employee, $doc]) }}"
                 class="btn btn-xs btn-outline-primary">
                <i class="gd-download icon-text"></i> Unduh
              </a>
              <small class="text-muted d-block" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $doc->original_name }}
              </small>
            </td>
            <td>
              @if($doc->expires_at)
                @if($doc->isExpired())
                  <span class="badge badge-danger">Kadaluarsa<br>{{ $doc->expires_at->format('d M Y') }}</span>
                @elseif($doc->isExpiringSoon())
                  <span class="badge badge-warning">Segera<br>{{ $doc->expires_at->format('d M Y') }}</span>
                @else
                  <span class="text-muted">{{ $doc->expires_at->format('d M Y') }}</span>
                @endif
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td><small class="text-muted">{{ $doc->notes ?? '—' }}</small></td>
            <td><small class="text-muted">{{ $doc->created_at->format('d/m/y') }}</small></td>
            <td>
              <form method="POST"
                    action="{{ route('appraisal.employees.documents.destroy', [$employee, $doc]) }}"
                    onsubmit="return confirm('Hapus dokumen ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-xs btn-outline-danger">
                  <i class="gd-trash icon-text"></i>
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
@endsection
