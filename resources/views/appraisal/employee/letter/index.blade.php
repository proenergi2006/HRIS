@extends('layouts.grain')
@section('title', 'Surat Karyawan')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Surat Karyawan</div>
  <div class="d-flex" style="gap:.5rem">
    <a href="{{ route('appraisal.letter-templates.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="gd-settings mr-1"></i> Template Surat
    </a>
    <a href="{{ route('appraisal.employee-letters.create') }}" class="btn btn-primary btn-sm">
      <i class="gd-plus mr-1"></i> Terbitkan Surat
    </a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">Karyawan</label>
        <select name="employee_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach($employees as $e)
            <option value="{{ $e->id }}" @selected(request('employee_id') == $e->id)>{{ $e->name }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if($letters->isEmpty())
      <p class="text-muted small mb-0">Belum ada surat diterbitkan.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>No. Surat</th><th>Karyawan</th><th>Judul</th><th>Kategori</th><th>Tgl Terbit</th><th></th></tr></thead>
          <tbody>
          @foreach($letters as $l)
            <tr>
              <td class="small">{{ $l->letter_number }}</td>
              <td>{{ $l->employee?->name }}</td>
              <td>{{ $l->title }}</td>
              <td>{{ $l->categoryLabel() }}</td>
              <td>{{ $l->issued_date?->format('d/m/Y') }}</td>
              <td>
                <a href="{{ route('appraisal.employee-letters.show', $l) }}" class="btn btn-xs btn-outline-secondary">Detail</a>
                <a href="{{ route('appraisal.employee-letters.pdf', $l) }}" class="btn btn-xs btn-outline-info"><i class="gd-download"></i></a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
