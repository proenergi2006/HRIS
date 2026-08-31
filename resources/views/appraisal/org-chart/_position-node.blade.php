{{-- 1 kotak Jabatan — SELALU kolaps jadi jumlah orang + klik utk popup nama
     & NIP (sama perlakuannya baik cuma 1 orang atau banyak, supaya kotak
     tidak pernah penuh nama). Strukturnya PERSIS sama dgn
     _department-node/_section-node (li > kotak + stub + ul) supaya garis
     penghubung konsisten di SELURUH bagan, bukan cuma sebagian. Kalau
     persis 1 orang & dia punya bawahan sendiri (field "Atasan Langsung"),
     bawahannya digambar rekursif di bawah kotak ini — dipisah dulu per
     Section kalau bawahannya berasal dari >1 Section berbeda. --}}
@php
    $single = $employees->count() === 1 ? $employees->first() : null;
    $reports = $single ? $reportsByManager->get($single->id, collect()) : collect();
    $position = $employees->first()->position;
    $popupId = 'org-pop-' . md5($employees->pluck('id')->implode('-'));
@endphp
<li>
  <div class="org-node org-emp org-emp-click" role="button" tabindex="0"
       onclick="openSiproModal('{{ $popupId }}')"
       onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSiproModal('{{ $popupId }}')}">
    <span class="org-emp-title">{{ $position->name ?? 'Tanpa Jabatan' }}</span>
    <span class="org-sub">{{ $employees->count() }} orang &rsaquo;</span>
  </div>

  @if($reports->isNotEmpty())
    <div class="org-dept-stub"></div>
    <ul>
      @php
        $bySection = $reports->groupBy(fn ($r) => $r->section_id ?: 0);
        $splitBySection = $bySection->count() > 1;
      @endphp
      @if($splitBySection)
        @foreach($bySection as $secReports)
          @php $section = $secReports->first()->section; @endphp
          <li>
            <div class="org-node org-section">
              {{ $section->name ?? 'Tanpa Section' }}
              <span class="org-sub">{{ $secReports->count() }} orang</span>
            </div>
            <div class="org-dept-stub"></div>
            <ul>
              @foreach($secReports->groupBy('position_id') as $posGroup)
                @include('appraisal.org-chart._position-node', ['employees' => $posGroup, 'reportsByManager' => $reportsByManager])
              @endforeach
            </ul>
          </li>
        @endforeach
      @else
        @foreach($reports->groupBy('position_id') as $posGroup)
          @include('appraisal.org-chart._position-node', ['employees' => $posGroup, 'reportsByManager' => $reportsByManager])
        @endforeach
      @endif
    </ul>
  @endif
</li>

@push('modals')
  <div class="sipro-overlay" id="{{ $popupId }}">
    <div class="sipro-backdrop" onclick="closeSiproModal('{{ $popupId }}')"></div>
    <div class="sipro-dialog" style="max-width:420px">
      <div class="sipro-header" style="background:#0F2A4A">
        <h5 style="color:#fff;margin:0;font-size:.95rem">
          <i class="gd-user mr-1"></i> {{ $position->name ?? 'Tanpa Jabatan' }}
        </h5>
        <button class="sipro-close" style="color:#fff" onclick="closeSiproModal('{{ $popupId }}')">&times;</button>
      </div>
      <div class="sipro-body p-0">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Nama</th><th>NIP</th></tr></thead>
          <tbody>
            @foreach($employees as $p)
              <tr><td>{{ $p->name }}</td><td class="text-muted">{{ $p->nip ?? '-' }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="sipro-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeSiproModal('{{ $popupId }}')">Tutup</button>
      </div>
    </div>
  </div>
@endpush
