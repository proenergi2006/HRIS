{{-- 1 kotak Section, dengan pohon Jabatan di bawahnya (pola sama Departemen). --}}
<li>
  <div class="org-node org-section"
       @if(($canEditOrg ?? false) && ($sec['section']->id ?? null))
         draggable="true" data-org-drag="section" data-org-id="{{ $sec['section']->id }}" data-org-name="{{ $sec['section']->name }}"
       @endif>
    {{ $sec['section']->name ?? 'Tanpa Section' }}
    <span class="org-sub">{{ $sec['total'] }} karyawan</span>
  </div>
  <div class="org-dept-stub"></div>
  <ul>
    @foreach($sec['levels'] as $lvl)
      @foreach($lvl['positions'] as $pos)
        @include('appraisal.org-chart._position-node', ['employees' => $pos['employees'], 'reportsByManager' => $reportsByManager])
      @endforeach
    @endforeach
    @foreach($sec['vacant'] as $vp)
      <li><div class="org-node org-emp org-emp-vacant">{{ $vp->name }}<span class="org-sub">— belum terisi —</span></div></li>
    @endforeach
  </ul>
</li>
