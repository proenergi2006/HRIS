@extends('layouts.grain')
@section('title', 'Permintaan Surat')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Permintaan Surat</div>
  @unless($isHr)
    <a href="{{ route('appraisal.letter-requests.create') }}" class="btn btn-primary btn-sm"><i class="gd-plus mr-1"></i> Ajukan Permintaan</a>
  @endunless
</div>

<div class="card">
  <div class="card-body p-0">
    @if($requests->isEmpty())
      <p class="text-muted small mb-0 p-3">Belum ada permintaan surat.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr>
            @if($isHr)<th>Karyawan</th>@endif
            <th>Template</th><th>Keperluan</th><th>Status</th><th></th>
          </tr></thead>
          <tbody>
          @foreach($requests as $r)
            <tr>
              @if($isHr)<td>{{ $r->employee?->name }}</td>@endif
              <td>{{ $r->template?->title ?? '—' }}</td>
              <td>{{ $r->purposeLabel() }}</td>
              <td>
                <span class="badge badge-{{ \App\Models\LetterRequest::$statusBadges[$r->status] ?? 'secondary' }}">{{ $r->statusLabel() }}</span>
                @if($r->status === 'processed' && $r->issuedLetter)
                  <a href="{{ route('appraisal.employee-letters.show', $r->issuedLetter) }}" class="small ml-1">lihat surat</a>
                @endif
                @if($r->status === 'rejected' && $r->rejection_note)
                  <div class="small text-danger">{{ $r->rejection_note }}</div>
                @endif
              </td>
              <td>
                @if($isHr && $r->status === 'pending')
                  <form method="POST" action="{{ route('appraisal.letter-requests.process', $r) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-xs btn-outline-primary">Proses</button>
                  </form>
                  <form method="POST" action="{{ route('appraisal.letter-requests.reject', $r) }}" class="d-inline" onsubmit="return confirm('Tolak permintaan ini?')">
                    @csrf
                    <button class="btn btn-xs btn-outline-danger">Tolak</button>
                  </form>
                @elseif(!$isHr && $r->status === 'pending')
                  <form method="POST" action="{{ route('appraisal.letter-requests.cancel', $r) }}" onsubmit="return confirm('Batalkan permintaan ini?')">
                    @csrf
                    <button class="btn btn-xs btn-outline-secondary">Batalkan</button>
                  </form>
                @endif
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
