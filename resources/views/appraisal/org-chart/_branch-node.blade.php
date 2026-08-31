{{-- 1 kotak Cabang (lokasi) — cabang ke Divisi (kalau cabang itu sendiri
     punya >1 Divisi) atau langsung ke Departemen/Direksi. Cabang HO biasanya
     menyimpan struktur lengkap kantor pusat (termasuk Direksi kalau ada),
     cabang lain umumnya tim kecil lokal. --}}
<li>
  <div class="org-node org-branch"
       data-org-drop="branch"
       data-org-id="{{ $branch['branch']->id ?? '' }}">
    {{ $branch['branch']->name ?? 'HO' }}
    <span class="org-sub">{{ $branch['total'] }} karyawan</span>
  </div>
  <div class="org-dept-stub"></div>
  <ul>
    @if($branch['showDivisionTier'])
      @foreach($branch['departments'] as $div)
        @include('appraisal.org-chart._division-node', ['div' => $div, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
      @endforeach
    @else
      @foreach($branch['departments'] as $item)
        @include('appraisal.org-chart._dept-or-direksi-node', ['item' => $item, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
      @endforeach
    @endif
  </ul>
</li>
