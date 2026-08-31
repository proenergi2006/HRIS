@extends('layouts.grain')
@section('title', 'Riwayat Persetujuan')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('approval.inbox.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">Riwayat Persetujuan</div>

<div class="row">
  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Pengajuan Saya</div>
      <div class="card-body">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Jenis</th><th>Ringkasan</th><th>Status</th></tr></thead>
          <tbody>
          @forelse($submitted as $r)
            <tr>
              <td class="small">{{ $r->type_label }}</td>
              <td class="small">{{ $r->summary }}</td>
              <td><span class="badge badge-{{ \App\Models\Approval\ApprovalRequest::$statusBadges[$r->status] ?? 'secondary' }}">{{ \App\Models\Approval\ApprovalRequest::$statusLabels[$r->status] ?? $r->status }}</span></td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-muted small py-2">Belum ada.</td></tr>
          @endforelse
          </tbody>
        </table>
        {{ $submitted->links() }}
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Yang Saya Tindak</div>
      <div class="card-body">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Jenis</th><th>Ringkasan</th><th>Aksi</th><th>Waktu</th></tr></thead>
          <tbody>
          @forelse($acted as $s)
            <tr>
              <td class="small">{{ $s->request->type_label }}</td>
              <td class="small">{{ $s->request->summary }}</td>
              <td><span class="badge badge-{{ $s->status === 'approved' ? 'success' : 'danger' }}">{{ ucfirst($s->status) }}</span></td>
              <td class="small">{{ $s->acted_at?->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-muted small py-2">Belum ada.</td></tr>
          @endforelse
          </tbody>
        </table>
        {{ $acted->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
