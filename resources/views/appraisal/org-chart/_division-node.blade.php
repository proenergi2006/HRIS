{{-- 1 kotak Divisi — cabang ke Departemen/Direksi di bawahnya. Dipakai baik
     langsung di bawah Company (tanpa Cabang) maupun di bawah 1 kotak Cabang. --}}
<li>
  <div class="org-node org-division"
       data-org-drop="division"
       data-org-id="{{ $div['division']->id ?? '' }}">
    {{ $div['division']->name ?? 'Tanpa Divisi' }}
    <span class="org-sub">{{ $div['total'] }} karyawan</span>
  </div>
  <div class="org-dept-stub"></div>
  <ul>
    @foreach($div['departments'] as $item)
      @include('appraisal.org-chart._dept-or-direksi-node', ['item' => $item, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
    @endforeach
  </ul>
</li>
