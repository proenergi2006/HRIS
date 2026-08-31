@extends('layouts.grain')
@section('title', 'Delegasi Persetujuan')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('approval.inbox.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-2">Delegasi Persetujuan</div>
<p class="text-muted small mb-4">Limpahkan tugas persetujuan Anda ke rekan lain selama rentang tanggal tertentu (mis. saat cuti).</p>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('approval.delegations.store') }}" class="form-row align-items-end mb-4">
      @csrf
      <div class="form-group col-md-4 mb-2">
        <label class="small text-muted mb-1">Delegasikan ke</label>
        <select name="delegate_user_id" class="form-control" required>
          <option value="">— pilih —</option>
          @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-2"><label class="small text-muted mb-1">Mulai</label><input type="date" name="start_date" class="form-control" required></div>
      <div class="form-group col-md-2 mb-2"><label class="small text-muted mb-1">Selesai</label><input type="date" name="end_date" class="form-control" required></div>
      <div class="form-group col-md-3 mb-2"><label class="small text-muted mb-1">Alasan</label><input type="text" name="reason" class="form-control"></div>
      <div class="form-group col-auto mb-2"><button class="btn btn-primary">Tambah</button></div>
    </form>

    <table class="table table-sm mb-0">
      <thead class="thead-light"><tr><th>Pengganti</th><th>Periode</th><th>Alasan</th><th style="width:60px"></th></tr></thead>
      <tbody>
      @forelse($delegations as $d)
        <tr>
          <td>{{ $d->delegate?->name }}</td>
          <td class="small">{{ $d->start_date?->format('d/m/Y') }} – {{ $d->end_date?->format('d/m/Y') }}</td>
          <td class="small">{{ $d->reason ?? '-' }}</td>
          <td><form method="POST" action="{{ route('approval.delegations.destroy', $d) }}" onsubmit="return confirm('Hapus delegasi?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="gd-trash"></i></button></form></td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-muted small py-2">Belum ada delegasi.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
