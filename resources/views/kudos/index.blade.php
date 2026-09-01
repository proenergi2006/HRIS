@extends('layouts.grain')
@section('title', 'Apresiasi / Kudos')

@section('content')
@include('components.notification')

<div class="h3 mb-3">Apresiasi &amp; Kudos</div>

<div class="row">
  <div class="col-md-8">
    @if($employee)
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Beri Apresiasi</div>
      <div class="card-body">
        <form method="POST" action="{{ route('kudos.store') }}">
          @csrf
          <div class="form-row">
            <div class="form-group col-md-5">
              <label class="small font-weight-bold">Untuk</label>
              <select name="to_employee_id" class="form-control form-control-sm" required>
                <option value="">— pilih rekan kerja —</option>
                @foreach($colleagues as $c)
                  <option value="{{ $c->id }}" @selected(old('to_employee_id') == $c->id)>{{ $c->name }} — {{ $c->position?->name ?? '-' }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-4">
              <label class="small font-weight-bold">Kategori</label>
              <select name="category" class="form-control form-control-sm" required>
                @foreach(\App\Models\HR\Kudos::$categoryLabels as $k => $lbl)
                  <option value="{{ $k }}" @selected(old('category') === $k)>{{ $lbl }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Pesan Apresiasi</label>
            <textarea name="message" rows="2" class="form-control form-control-sm" maxlength="500" placeholder="Ceritakan hal baik yang dilakukan rekan Anda..." required>{{ old('message') }}</textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-sm"><i class="gd-heart mr-1"></i>Kirim Apresiasi</button>
        </form>
      </div>
    </div>
    @else
    <div class="alert alert-secondary small">Akun Anda belum terhubung ke data karyawan — tidak bisa memberi apresiasi, tapi tetap bisa melihat wall.</div>
    @endif

    <div class="card">
      <div class="card-header font-weight-bold">Kudos Wall</div>
      <div class="card-body p-0">
        @forelse($feed as $k)
          <div class="d-flex align-items-start px-3 py-3 border-bottom">
            <div class="mr-3 mt-1"><i class="{{ \App\Models\HR\Kudos::$categoryIcons[$k->category] ?? 'gd-star' }}" style="font-size:1.3rem;color:#0F2A4A"></i></div>
            <div class="flex-grow-1">
              <div class="small">
                <strong>{{ $k->fromEmployee?->name ?? 'Seseorang' }}</strong>
                &rarr; <strong>{{ $k->toEmployee?->name ?? '-' }}</strong>
                <span class="badge badge-light ml-1">{{ \App\Models\HR\Kudos::$categoryLabels[$k->category] ?? $k->category }}</span>
              </div>
              <div class="text-muted small mt-1">{{ $k->message }}</div>
              <div class="text-muted mt-1" style="font-size:.72rem"><i class="gd-time mr-1"></i>{{ $k->created_at->diffForHumans() }}</div>
            </div>
          </div>
        @empty
          <div class="text-muted small p-4 text-center">Belum ada apresiasi. Jadilah yang pertama memberi kudos ke rekan kerja!</div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header font-weight-bold">Terbanyak Bulan Ini</div>
      <div class="card-body p-0">
        @forelse($leaderboard as $i => $l)
          <div class="d-flex align-items-center justify-content-between px-3 py-2 {{ $loop->last ? '' : 'border-bottom' }}">
            <div class="small"><span class="text-muted mr-2">{{ $i + 1 }}.</span>{{ $l->toEmployee?->name ?? '-' }}</div>
            <span class="badge badge-primary">{{ $l->total }}</span>
          </div>
        @empty
          <div class="text-muted small p-3 text-center">Belum ada data bulan ini.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
