{{-- 1 kotak Departemen — kalau punya Section, cabang ke Section; kalau
     tidak, langsung ke pohon Jabatan (li > kotak + stub + ul, sama seperti
     Section/Jabatan) supaya garis penghubung konsisten sampai ke akar. --}}
<li>
  <div class="org-node org-dept"
       data-org-drop="department"
       data-org-id="{{ $dept['department']->id ?? '' }}"
       @if(($canEditOrg ?? false) && ($dept['department']->id ?? null))
         draggable="true" data-org-drag="department" data-org-name="{{ $dept['department']->name }}"
       @endif>
    {{ $dept['department']->name ?? 'Tanpa Departemen' }}
    <span class="org-sub">{{ $dept['total'] }} karyawan</span>
  </div>
  <div class="org-dept-stub"></div>
  <ul>
    @if($dept['sections'])
      @foreach($dept['sections'] as $sec)
        @include('appraisal.org-chart._section-node', ['sec' => $sec, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
      @endforeach
    @else
      @foreach($dept['levels'] as $lvl)
        @foreach($lvl['positions'] as $pos)
          @include('appraisal.org-chart._position-node', ['employees' => $pos['employees'], 'reportsByManager' => $reportsByManager])
        @endforeach
      @endforeach
      @foreach($dept['vacant'] as $vp)
        <li><div class="org-node org-emp org-emp-vacant">{{ $vp->name }}<span class="org-sub">— belum terisi —</span></div></li>
      @endforeach
    @endif
  </ul>
</li>
