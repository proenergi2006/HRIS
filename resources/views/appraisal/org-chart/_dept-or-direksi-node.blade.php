{{-- Dispatcher: satu 'item' di tier ini boleh berupa kotak Direksi (CEO/CFO/
     dst) atau kotak Departemen biasa — buildDepartmentsOrDireksi() menandai
     tiap item dgn 'type' supaya di sini tinggal dipilih partial yang tepat.
     Dipakai di SEMUA cakupan (root, per-Cabang, per-Divisi) supaya perlakuan
     Direksi konsisten di seluruh bagan. --}}
@if(($item['type'] ?? 'department') === 'direksi')
  @include('appraisal.org-chart._direksi-node', ['node' => $item, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
@else
  @include('appraisal.org-chart._department-node', ['dept' => $item, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg ?? false])
@endif
