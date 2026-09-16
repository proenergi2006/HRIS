{{-- 1 kotak Direksi (CEO/CFO/dst) — bercabang ke departemen yang manager
     root-nya lapor ke dia, dan/atau ke Direksi lain yg lapor ke dia (mis.
     CFO -> CEO). Pola sama persis Departemen/Section/Jabatan (li > kotak +
     stub + ul) supaya garis penghubung konsisten sampai ke akar. --}}
@php
    $employee = $node['employee'];
    $popupId  = 'org-pop-direksi-' . $employee->id;
    $childDivisions = $node['childDivisions'] ?? collect();
    $childBranches  = $node['childBranches'] ?? collect();
    $hasChildren = $node['departments']->isNotEmpty() || $node['children']->isNotEmpty()
        || $childDivisions->isNotEmpty() || $childBranches->isNotEmpty();
@endphp
<li>
  <div class="org-node org-direksi org-emp-click" role="button" tabindex="0"
       onclick="openSiproModal('{{ $popupId }}')"
       onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSiproModal('{{ $popupId }}')}">
    <span class="org-emp-title">{{ $employee->position->name ?? 'Direksi' }}</span>
    <span class="org-sub">1 orang &rsaquo;</span>
  </div>

  @if($hasChildren)
    <div class="org-dept-stub"></div>
    <ul>
      @foreach($node['children'] as $child)
        @include('appraisal.org-chart._direksi-node', ['node' => $child, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
      @endforeach
      @foreach($childDivisions as $div)
        @include('appraisal.org-chart._division-node', ['div' => $div, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
      @endforeach
      @foreach($childBranches as $branch)
        @include('appraisal.org-chart._branch-node', ['branch' => $branch, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
      @endforeach
      @foreach($node['departments'] as $dept)
        @include('appraisal.org-chart._department-node', ['dept' => $dept, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
      @endforeach
    </ul>
  @endif
</li>

@push('modals')
  <div class="sipro-overlay" id="{{ $popupId }}">
    <div class="sipro-backdrop" onclick="closeSiproModal('{{ $popupId }}')"></div>
    <div class="sipro-dialog" style="max-width:420px">
      <div class="sipro-header" style="background:#0F2A4A">
        <h5 style="color:#fff;margin:0;font-size:.95rem">
          <i class="gd-user mr-1"></i> {{ $employee->position->name ?? 'Direksi' }}
        </h5>
        <button class="sipro-close" style="color:#fff" onclick="closeSiproModal('{{ $popupId }}')">&times;</button>
      </div>
      <div class="sipro-body p-0">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Nama</th><th>NIP</th></tr></thead>
          <tbody>
            <tr><td>{{ $employee->name }}</td><td class="text-muted">{{ $employee->nip ?? '-' }}</td></tr>
          </tbody>
        </table>
      </div>
      <div class="sipro-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeSiproModal('{{ $popupId }}')">Tutup</button>
      </div>
    </div>
  </div>
@endpush
