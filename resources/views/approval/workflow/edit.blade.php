@extends('layouts.grain')
@section('title', 'Atur Alur — ' . $typeLabel)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('approval.workflows.index', ['company' => $company->id]) }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-1">{{ $typeLabel }}</div>
<p class="text-muted mb-4">{{ $company->short_name ?? $company->name }}</p>

<div class="row">
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Langkah Persetujuan</div>
      <div class="card-body">
        <form method="POST" action="{{ route('approval.workflows.update', [$company, $type]) }}" id="wf-form">
          @csrf @method('PUT')
          <div id="steps">
            @forelse($steps as $i => $s)
              @include('approval.workflow._step', ['i' => $i, 's' => $s])
            @empty
            @endforelse
          </div>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="addStep()"><i class="gd-plus mr-1"></i>Tambah Step</button>
          <hr>
          <button type="submit" class="btn btn-primary">Simpan Alur</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header font-weight-bold">Simulasi</div>
      <div class="card-body">
        <p class="text-muted small">Cek siapa yang akan menyetujui jika seorang karyawan mengajukan transaksi ini.</p>
        <form method="POST" action="{{ route('approval.workflows.simulate', [$company, $type]) }}">
          @csrf
          <div class="form-group">
            <label class="small">Karyawan</label>
            <select name="employee_id" class="form-control form-control-sm" required>
              <option value="">— pilih —</option>
              @foreach(\App\Models\Employee::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get() as $emp)
                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="small">Atribut (opsional)</label>
            <input type="text" name="attributes" class="form-control form-control-sm" placeholder="total_days=5, sanction_level=sp3">
          </div>
          <button class="btn btn-sm btn-outline-secondary btn-block">Simulasikan</button>
        </form>

        @if(session('simulation'))
          @php $sim = session('simulation'); @endphp
          <hr>
          <div class="small font-weight-bold mb-2">{{ $sim['employee'] }}:</div>
          <ol class="pl-3 small mb-0">
            @foreach($sim['steps'] as $st)
              <li class="mb-1 {{ $st['applies'] ? '' : 'text-muted' }}">
                {{ $st['label'] }} → <strong>{{ $st['approver'] }}</strong>
                @unless($st['applies'])<em>(kondisi tidak terpenuhi — di-skip)</em>@endunless
              </li>
            @endforeach
          </ol>
        @endif
      </div>
    </div>
  </div>
</div>

<template id="step-tpl">
  @include('approval.workflow._step', ['i' => '__I__', 's' => null])
</template>

<script>
  window.WF_APPROVER_TYPES = @json($approverTypes);
  let stepIndex = {{ $steps->count() }};
  function addStep() {
    const tpl = document.getElementById('step-tpl').innerHTML.replaceAll('__I__', stepIndex++);
    document.getElementById('steps').insertAdjacentHTML('beforeend', tpl);
  }
  function removeStep(btn) { btn.closest('.wf-step').remove(); }
  function toggleApproverRef(sel) {
    const wrap = sel.closest('.wf-step');
    wrap.querySelector('.ref-position').style.display = sel.value === 'specific_position' ? '' : 'none';
    wrap.querySelector('.ref-role').style.display     = sel.value === 'specific_role' ? '' : 'none';
  }
  document.querySelectorAll('.wf-step select[name$="[approver_type]"]').forEach(toggleApproverRef);
  @if($steps->isEmpty()) addStep(); @endif
</script>
@endsection
