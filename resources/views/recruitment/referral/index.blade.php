@extends('layouts.grain')
@section('title', 'Referensikan Kandidat')

@section('content')
@include('components.notification')

<div class="h3 mb-3">Employee Referral — Referensikan Kandidat</div>

<div class="alert alert-info small mb-4">
  Punya kenalan yang cocok untuk lowongan terbuka? Referensikan di sini. Tim rekrutmen akan
  menghubungi kandidat dan memprosesnya lewat jalur seleksi normal. Bonus referral (jika ada)
  akan diproses HR setelah kandidat resmi bergabung.
</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Referensikan Kandidat Baru</div>
  <div class="card-body">
    @if($openRequisitions->isEmpty())
      <p class="text-muted small mb-0">Tidak ada lowongan terbuka di PT Anda saat ini.</p>
    @else
    <form method="POST" action="{{ route('recruitment.referrals.store') }}">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-4">
          <label class="small font-weight-bold">Lowongan Dituju <span class="text-danger">*</span></label>
          <select name="job_requisition_id" class="form-control form-control-sm" required>
            <option value="">— pilih —</option>
            @foreach($openRequisitions as $jr)
              <option value="{{ $jr->id }}">{{ $jr->title }} — {{ $jr->department?->name ?? '-' }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label class="small font-weight-bold">Nama Kandidat <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control form-control-sm" required>
        </div>
        <div class="form-group col-md-3">
          <label class="small font-weight-bold">Email</label>
          <input type="email" name="email" class="form-control form-control-sm">
        </div>
        <div class="form-group col-md-2">
          <label class="small font-weight-bold">Telepon</label>
          <input type="text" name="phone" class="form-control form-control-sm">
        </div>
      </div>
      <div class="form-group">
        <label class="small font-weight-bold">Catatan (opsional)</label>
        <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Kenapa Anda merekomendasikan kandidat ini?"></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Kirim Referensi</button>
    </form>
    @endif
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Referral Saya ({{ $myReferrals->count() }})</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Kandidat</th><th>Lowongan</th><th>Status</th><th class="text-right">Bonus Referral</th></tr></thead>
        <tbody>
        @forelse($myReferrals as $c)
          <tr>
            <td>{{ $c->name }}</td>
            <td class="small text-muted">{{ $c->jobRequisition?->title ?? '-' }}</td>
            <td><span class="badge badge-{{ \App\Models\Candidate::$statusBadges[$c->status] ?? 'secondary' }}">{{ \App\Models\Candidate::$statusLabels[$c->status] ?? $c->status }}</span></td>
            <td class="text-right small">
              @if($c->referral_bonus_paid_at)
                <span class="text-success">Rp {{ number_format($c->referral_bonus_amount,0,',','.') }} — dibayar {{ $c->referral_bonus_paid_at->format('d/m/Y') }}</span>
              @elseif($c->referral_bonus_amount)
                <span class="text-muted">Rp {{ number_format($c->referral_bonus_amount,0,',','.') }} — belum dibayar</span>
              @else <span class="text-muted">—</span> @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-muted small p-3">Belum ada referral yang dikirim.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
