@extends('layouts.grain')
@section('title', 'Struktur Organisasi')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Struktur Organisasi</div>
  @if($selectedCompany && $tree->isNotEmpty())
    <div>
      <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportOrgChartImage(this)">
        <i class="gd-image mr-1"></i> Export Gambar
      </button>
      <a href="{{ route('appraisal.org-chart.pdf', array_filter(['company' => $selectedCompany->id] + $filters)) }}"
         class="btn btn-outline-danger btn-sm">
        <i class="gd-file mr-1"></i> Export PDF
      </a>
    </div>
  @endif
</div>

{{-- Company tabs --}}
<div class="card mb-3">
  <div class="card-body py-2">
    <ul class="nav nav-tabs" style="border-bottom:none">
      @foreach($companies as $c)
        <li class="nav-item">
          <a class="nav-link {{ $selectedCompany && $selectedCompany->id === $c->id ? 'active' : '' }}"
             href="{{ route('appraisal.org-chart.index', ['company' => $c->id]) }}">
            {{ $c->name }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</div>

@if($selectedCompany && ($branches->isNotEmpty() || $divisions->isNotEmpty() || $departments->isNotEmpty() || $sections->isNotEmpty()))
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="GET" class="form-row align-items-end">
      <input type="hidden" name="company" value="{{ $selectedCompany->id }}">
      @if($branches->isNotEmpty())
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Cabang</label>
          <select name="branch" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">Semua Cabang</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}" @selected($filters['branch'] === $b->id)>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
      @endif
      <div class="form-group col-6 col-md-3 mb-2">
        <label class="small text-muted mb-1">Divisi</label>
        <select name="division" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">Semua Divisi</option>
          @foreach($divisions as $d)
            <option value="{{ $d->id }}" @selected($filters['division'] === $d->id)>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-6 col-md-3 mb-2">
        <label class="small text-muted mb-1">Departemen</label>
        <select name="department" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">Semua Departemen</option>
          @foreach($departments as $d)
            <option value="{{ $d->id }}" @selected($filters['department'] === $d->id)>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-6 col-md-3 mb-2">
        <label class="small text-muted mb-1">Section</label>
        <select name="section" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">Semua Section</option>
          @foreach($sections as $s)
            <option value="{{ $s->id }}" @selected($filters['section'] === $s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
      </div>
      @if($filters['branch'] || $filters['division'] || $filters['department'] || $filters['section'])
        <div class="form-group col-6 col-md-3 mb-2">
          <a href="{{ route('appraisal.org-chart.index', ['company' => $selectedCompany->id]) }}" class="btn btn-outline-secondary btn-sm btn-block">
            <i class="gd-close mr-1"></i>Reset Filter
          </a>
        </div>
      @endif
    </form>
  </div>
</div>
@endif

@if(!$selectedCompany)
  <div class="alert alert-warning">Belum ada data perusahaan aktif.</div>
@elseif($tree->isEmpty())
  <div class="card">
    <div class="card-body text-center text-muted py-5">
      <i class="gd-user d-block mb-2" style="font-size:2rem;opacity:.3"></i>
      Belum ada data karyawan aktif untuk <strong>{{ $selectedCompany->name }}</strong>{{ ($filters['branch'] || $filters['division'] || $filters['department'] || $filters['section']) ? ' pada filter ini' : '' }}.
    </div>
  </div>
@else
<div class="card">
  <div class="card-body">
    <style>
      .org-chart { overflow-x: auto; padding: 30px 10px 10px; }
      .org-chart ul {
        padding-top: 24px; position: relative;
        display: flex; justify-content: center;
        justify-content: safe center; /* fallback dulu, browser modern override ke sini */
        margin: 0;
      }
      .org-chart li {
        list-style: none; text-align: center;
        position: relative; padding: 24px 14px 0 14px;
      }
      .org-chart li::before, .org-chart li::after {
        content: ''; position: absolute; top: 0; right: 50%;
        border-top: 2px solid #cbd5e1; width: 51%; height: 24px;
      }
      .org-chart li::after { right: auto; left: 50%; border-left: 2px solid #cbd5e1; }
      .org-chart li:only-child::after, .org-chart li:only-child::before { display: none; }
      .org-chart li:only-child { padding-top: 0; }
      .org-chart li:first-child::before, .org-chart li:last-child::after { border: 0 none; }
      .org-chart li:last-child::before { border-right: 2px solid #cbd5e1; border-radius: 0 6px 0 0; }
      .org-chart li:first-child::after { border-radius: 6px 0 0 0; }
      .org-chart ul ul::before {
        content: ''; position: absolute; top: 0; left: 50%;
        border-left: 2px solid #cbd5e1; width: 0; height: 24px;
      }
      .org-node {
        display: inline-block; border-radius: 8px; padding: 10px 16px;
        min-width: 150px; font-size: .82rem; text-align: left;
      }
      .org-node.org-root {
        background: linear-gradient(135deg, #0d2137, #1a4a8a);
        color: #fff; text-align: center; font-weight: 800;
        letter-spacing: .5px; min-width: 220px; padding: 14px 22px;
      }
      {{-- Sub-teks kotak pakai <span class="org-sub"> (bukan <small>) —
           html2canvas 1.4.1 kerap gagal menggambar teks <small display:block>
           saat "Export Gambar", <span> aman. --}}
      .org-sub { display: block; font-weight: 400; margin-top: 2px; font-size: .92em; opacity: .85; }
      .org-node.org-dept {
        background: #eef3fb; border: 1px solid #cfe0f5;
        font-weight: 700; color: #1a4a8a; text-align: center;
      }
      .org-node.org-dept .org-sub { color: #6b7280; }
      .org-node.org-division {
        background: #0F2A4A; border: 1px solid #0d2137;
        font-weight: 700; color: #fff; text-align: center;
      }
      .org-node.org-division .org-sub { color: #cbd5e1; }
      .org-node.org-section {
        background: #fef3e2; border: 1px solid #f6dcb0;
        font-weight: 700; color: #92600a; text-align: center;
      }
      .org-node.org-section .org-sub { color: #a8895a; }
      .org-node.org-branch {
        background: #f0fdf4; border: 1px solid #bbf7d0;
        font-weight: 700; color: #166534; text-align: center;
      }
      .org-node.org-branch .org-sub { color: #4d8a63; }
      .org-node.org-direksi {
        background: linear-gradient(135deg, #7c2d12, #b45309);
        border: 1px solid #92400e; color: #fff; text-align: center; font-weight: 700;
      }
      .org-node.org-direksi .org-sub { color: #fde68a; }
      .org-node.org-direksi.org-emp-click:hover { border-color: #fed7aa; }

      .org-dept-stub { width: 0; height: 14px; margin: 0 auto; border-left: 2px solid #cbd5e1; }

      {{-- Kotak Jabatan — SELALU "N orang" + klik utk popup, baik 1 orang maupun
           banyak. Sama-sama <li> dgn Departemen/Section supaya garis penghubung
           konsisten dari akar sampai daun di SELURUH bagan, tidak ada lagi
           tier yang pakai gaya indentasi berbeda. Kotak (div) langsung yang
           di-klik — bukan <a> — supaya teks selalu ikut ter-render saat
           "Export Gambar" (html2canvas gagal menggambar teks di <a display:block). --}}
      .org-node.org-emp { background: #f8fafc; border: 1px solid #e2e8f0; color: #1a4a8a; }
      .org-node.org-emp .org-emp-title { font-weight: 600; }
      .org-node.org-emp .org-sub { color: #6b7280; }
      .org-node.org-emp-vacant { background: #fff; border-style: dashed; color: #9ca3af; font-style: italic; font-weight: 400; }
      .org-node.org-emp-vacant .org-sub { color: #9ca3af; }
      .org-emp-click { cursor: pointer; }
      .org-emp-click:hover { border-color: #1a4a8a; }
      .org-emp-click:focus-visible { outline: 2px solid #1a4a8a; outline-offset: 1px; }
      [data-org-drag] { cursor: grab; }
      [data-org-drag].org-dragging { opacity: .4; }
      .org-drop-ok { outline: 3px dashed #16a34a; outline-offset: 2px; }
    </style>

    @if($canEditOrg)
      <div class="alert alert-info py-2 px-3 small mb-3">
        <i class="gd-info mr-1"></i> Mode edit: seret kotak <strong>Departemen</strong> ke Divisi lain, atau kotak
        <strong>Section</strong> ke Departemen lain untuk memindahkannya. Perubahan tercatat di Riwayat Perubahan Organisasi.
      </div>
    @endif

    <div class="org-chart">
      <ul>
        <li>
          <div class="org-node org-root">{{ $selectedCompany->name }}</div>
          <ul>
            @if($showBranchTier)
              @foreach($tree as $branch)
                @include('appraisal.org-chart._branch-node', ['branch' => $branch, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg])
              @endforeach
            @elseif($showDivisionTier)
              @foreach($tree as $div)
                @include('appraisal.org-chart._division-node', ['div' => $div, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg])
              @endforeach
            @else
              @foreach($tree as $item)
                @include('appraisal.org-chart._dept-or-direksi-node', ['item' => $item, 'reportsByManager' => $reportsByManager, 'canEditOrg' => $canEditOrg])
              @endforeach
            @endif
          </ul>
        </li>
      </ul>
    </div>
    <small class="text-muted d-block text-center mt-2">Geser ke samping kalau struktur lebih lebar dari layar.</small>
  </div>
</div>
<script src="{{ asset('vendor/html2canvas/html2canvas.min.js') }}"></script>
<script>
(function () {
  // Browser lama yang belum dukung justify-content:"safe center" akan
  // clip + tidak bisa di-scroll ke arah kiri saat konten flex ter-center
  // melebihi lebar container. Fallback: kalau meluber, pindah ke flex-start
  // supaya seluruh isi tetap terjangkau lewat scroll.
  if (window.CSS && CSS.supports && CSS.supports('justify-content', 'safe center')) return;

  function adjust() {
    var wrap = document.querySelector('.org-chart');
    if (!wrap) return;
    wrap.querySelectorAll('ul').forEach(function (ul) {
      ul.style.justifyContent = ul.scrollWidth > wrap.clientWidth ? 'flex-start' : 'center';
    });
  }
  adjust();
  window.addEventListener('resize', adjust);
})();

function exportOrgChartImage(btn) {
  var el = document.querySelector('.org-chart');
  if (!el) return;
  if (!window.html2canvas) {
    alert('Fitur export gambar belum siap dimuat, coba lagi sesaat lagi.');
    return;
  }

  // .org-chart pakai overflow-x:auto — html2canvas cuma menangkap area yang
  // terlihat kalau ukurannya tidak dipaksa. Sementara ekspor: lebarkan
  // container ke ukuran penuh isinya + matikan scroll, lalu kembalikan lagi.
  var label = btn ? btn.innerHTML : null;
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="gd-loader mr-1"></i> Memproses...'; }

  var fullW = el.scrollWidth;
  var fullH = el.scrollHeight;
  var prev = { overflow: el.style.overflow, width: el.style.width };
  el.style.overflow = 'visible';
  el.style.width = fullW + 'px';

  function restore() {
    el.style.overflow = prev.overflow;
    el.style.width = prev.width;
    if (btn) { btn.disabled = false; btn.innerHTML = label; }
  }

  html2canvas(el, {
    scale: 2,
    backgroundColor: '#ffffff',
    width: fullW,
    height: fullH,
    windowWidth: fullW + 80,
    scrollX: 0,
    scrollY: -window.scrollY,
  }).then(function (canvas) {
    restore();
    var link = document.createElement('a');
    link.download = 'struktur-organisasi-{{ \Illuminate\Support\Str::slug($selectedCompany->short_name ?: $selectedCompany->name) }}.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
  }).catch(function (err) {
    restore();
    alert('Gagal membuat gambar: ' + (err && err.message ? err.message : err));
  });
}
</script>

@if($canEditOrg)
<script>
(function () {
  var CSRF = '{{ csrf_token() }}';
  var URLS = {
    department: "{{ url('appraisal/departments') }}/:id/reparent",
    section:    "{{ url('appraisal/sections') }}/:id/reparent",
  };
  // apa yang boleh di-drop ke mana: {jenis yang diseret: atribut data-* di target}
  var TARGET = { department: 'division', section: 'department' };
  var PAYLOAD_KEY = { department: 'division_id', section: 'department_id' };

  var dragged = null;

  document.querySelectorAll('[data-org-drag]').forEach(function (node) {
    node.addEventListener('dragstart', function (e) {
      dragged = { type: node.dataset.orgDrag, id: node.dataset.orgId, name: node.dataset.orgName };
      node.classList.add('org-dragging');
      e.dataTransfer.effectAllowed = 'move';
    });
    node.addEventListener('dragend', function () {
      node.classList.remove('org-dragging');
      document.querySelectorAll('.org-drop-ok').forEach(function (n) { n.classList.remove('org-drop-ok'); });
      dragged = null;
    });
  });

  function validTarget(el) {
    if (!dragged) return null;
    var want = TARGET[dragged.type];
    var t = el.closest('[data-org-drop="' + want + '"]');
    if (!t) return null;
    // jangan izinkan drop kotak ke dirinya sendiri
    if (t === el.closest('[data-org-drag]')) return null;
    return t;
  }

  document.querySelectorAll('[data-org-drop]').forEach(function (target) {
    target.addEventListener('dragover', function (e) {
      var t = validTarget(target);
      if (t) { e.preventDefault(); t.classList.add('org-drop-ok'); }
    });
    target.addEventListener('dragleave', function () { target.classList.remove('org-drop-ok'); });
    target.addEventListener('drop', function (e) {
      e.preventDefault();
      var t = validTarget(target);
      if (!t || !dragged) return;
      var newParentId = t.dataset.orgId || '';
      var newParentName = t.textContent.trim().split('\n')[0];
      if (!confirm('Pindahkan "' + dragged.name + '" ke "' + newParentName + '"?')) return;

      var body = {};
      body[PAYLOAD_KEY[dragged.type]] = newParentId;

      fetch(URLS[dragged.type].replace(':id', dragged.id), {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(body),
      }).then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        location.reload();
      }).catch(function (err) {
        alert('Gagal memindahkan: ' + err.message);
      });
    });
  });
})();
</script>
@endif
@endif
@endsection
