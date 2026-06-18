<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIPRO') }} - @yield('title')</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='18' fill='%230f2a4a'/><text x='50' y='70' font-family='Arial,sans-serif' font-size='46' font-weight='bold' text-anchor='middle' fill='white'>SP</text></svg>">

    <!-- Styles -->
    <link href="{{ asset('graindashboard/css/graindashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @yield('styles')
</head>

  <body class="has-sidebar has-fixed-sidebar-and-header">
	@include('components.header')

    <main class="main">
	  @include('components.sidebar')

      <div class="content">
        <div class="py-4 px-3 px-md-4">

			@yield('content')

        </div>

		@include('components.footer')

      </div>
    </main>

	<script src="{{ asset('graindashboard/js/graindashboard.js') }}"></script>
	<script src="{{ asset('graindashboard/js/graindashboard.vendor.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
    window.siproDtLang = {
        lengthMenu: 'Tampilkan _MENU_ data',
        zeroRecords: 'Tidak ada data yang ditemukan',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        infoFiltered: '(difilter dari _MAX_ total data)',
        search: 'Cari:',
        paginate: { first:'Pertama', last:'Terakhir', next:'&rsaquo;', previous:'&lsaquo;' },
        emptyTable: 'Tidak ada data tersedia'
    };
    </script>
    @yield('scripts')

    {{-- Custom SIPRO modals — di level body, di luar semua stacking context --}}
    @stack('modals')

    {{-- Confirm modal (reusable, diisi via JS) --}}
    <div class="sipro-overlay" id="sipro-confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="sipro-confirm-title">
      <div class="sipro-backdrop" onclick="closeSiproModal('sipro-confirm-overlay')"></div>
      <div class="sipro-dialog" style="max-width:420px;">
        <div class="sipro-header">
          <h5 id="sipro-confirm-title" style="display:flex;align-items:center;gap:8px;">
            <span id="sipro-confirm-icon"></span>
            <span id="sipro-confirm-title-text">Konfirmasi</span>
          </h5>
          <button class="sipro-close" onclick="closeSiproModal('sipro-confirm-overlay')" aria-label="Tutup">&times;</button>
        </div>
        <div class="sipro-body">
          <p id="sipro-confirm-message" style="margin:0;color:#374151;line-height:1.6;"></p>
        </div>
        <div class="sipro-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeSiproModal('sipro-confirm-overlay')">Batal</button>
          <button type="button" class="btn btn-sm" id="sipro-confirm-btn">Konfirmasi</button>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SIPRO UI SYSTEM — Toast + Loading + Modal
    ════════════════════════════════════════════════════════════ --}}

    {{-- Loading overlay --}}
    <div id="sipro-loading" aria-hidden="true">
      <div class="sipro-loading-box">
        <div class="sipro-spinner">
          <div></div><div></div><div></div><div></div>
          <div></div><div></div><div></div><div></div>
        </div>
        <div class="sipro-loading-text">Memproses...</div>
      </div>
    </div>

    {{-- Toast container --}}
    <div id="sipro-toasts" aria-live="polite"></div>

    <style>
      /* ── Timeline ── */
      .sipro-timeline{padding-left:8px;}
      .sipro-tl-item{display:flex;gap:16px;padding-bottom:20px;position:relative;}
      .sipro-tl-item:not(:last-child)::before{content:'';position:absolute;left:7px;top:16px;bottom:0;width:2px;background:#e2e8f0;}
      .sipro-tl-dot{width:16px;height:16px;border-radius:50%;flex-shrink:0;margin-top:2px;border:2px solid #fff;box-shadow:0 0 0 2px currentColor;}
      .sipro-tl-success{background:#22c55e;color:#22c55e;}
      .sipro-tl-danger {background:#ef4444;color:#ef4444;}
      .sipro-tl-primary{background:#3b82f6;color:#3b82f6;}
      .sipro-tl-info   {background:#0ea5e9;color:#0ea5e9;}
      .sipro-tl-muted  {background:#94a3b8;color:#94a3b8;}
      .sipro-tl-body{flex:1;padding-top:0;}
      .sipro-tl-title{font-size:.875rem;font-weight:600;color:#1e293b;line-height:1.3;}
      .sipro-tl-meta{font-size:.775rem;color:#64748b;margin-top:2px;}
      .sipro-tl-note{font-size:.8rem;color:#475569;background:#f8fafc;border-left:3px solid #e2e8f0;padding:6px 10px;margin-top:6px;border-radius:0 4px 4px 0;font-style:italic;}

      /* ── Modal ── */
      .sipro-overlay{position:fixed;inset:0;z-index:9990;display:none;align-items:center;justify-content:center;}
      .sipro-overlay.is-open{display:flex;}
      .sipro-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.45);}
      .sipro-dialog{position:relative;z-index:1;background:#fff;border-radius:8px;width:520px;max-width:calc(100vw - 32px);max-height:90vh;overflow-y:auto;box-shadow:0 12px 40px rgba(0,0,0,.18);}
      .sipro-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e9ecef;}
      .sipro-header h5{margin:0;font-size:1rem;font-weight:700;}
      .sipro-body{padding:20px;}
      .sipro-footer{padding:12px 20px;border-top:1px solid #e9ecef;display:flex;justify-content:flex-end;gap:8px;}
      .sipro-close{background:none;border:none;font-size:1.4rem;line-height:1;cursor:pointer;color:#6c757d;padding:0;}
      .sipro-close:hover{color:#000;}

      /* ── Client-side validation ── */
    .was-validated .form-control:invalid,.form-control.is-invalid{border-color:#ef4444!important;}
    .was-validated .form-control:valid{border-color:#22c55e!important;}
    .field-hint{font-size:.775rem;color:#64748b;margin-top:4px;}

    /* ── Loading overlay ── */
      #sipro-loading{
        position:fixed;inset:0;z-index:99999;
        background:rgba(255,255,255,.72);
        backdrop-filter:blur(3px);
        display:flex;align-items:center;justify-content:center;
        opacity:0;pointer-events:none;
        transition:opacity .2s;
      }
      #sipro-loading.is-active{opacity:1;pointer-events:all;}
      .sipro-loading-box{text-align:center;}
      .sipro-loading-text{font-size:.8rem;font-weight:600;color:#64748b;margin-top:14px;letter-spacing:.5px;text-transform:uppercase;}
      /* 8-dot spinner */
      .sipro-spinner{position:relative;width:44px;height:44px;margin:0 auto;}
      .sipro-spinner div{
        position:absolute;width:8px;height:8px;border-radius:50%;
        background:#1a3f6f;animation:sipro-spin 1.2s linear infinite;
      }
      .sipro-spinner div:nth-child(1){top:0;left:18px;animation-delay:0s;}
      .sipro-spinner div:nth-child(2){top:5px;left:33px;animation-delay:-.15s;}
      .sipro-spinner div:nth-child(3){top:18px;left:37px;animation-delay:-.3s;}
      .sipro-spinner div:nth-child(4){top:33px;left:33px;animation-delay:-.45s;}
      .sipro-spinner div:nth-child(5){top:37px;left:18px;animation-delay:-.6s;}
      .sipro-spinner div:nth-child(6){top:33px;left:4px;animation-delay:-.75s;}
      .sipro-spinner div:nth-child(7){top:18px;left:0;animation-delay:-.9s;}
      .sipro-spinner div:nth-child(8){top:5px;left:4px;animation-delay:-1.05s;}
      @keyframes sipro-spin{
        0%,80%,100%{opacity:.15;transform:scale(.75);}
        40%{opacity:1;transform:scale(1);}
      }

      /* ── Toast container ── */
      #sipro-toasts{
        position:fixed;top:20px;right:20px;z-index:99998;
        display:flex;flex-direction:column;gap:10px;
        width:320px;max-width:calc(100vw - 32px);
        pointer-events:none;
      }
      /* ── Single toast ── */
      .sipro-toast{
        background:#fff;
        border-radius:10px;
        box-shadow:0 8px 28px rgba(0,0,0,.14);
        padding:14px 16px;
        display:flex;align-items:flex-start;gap:12px;
        pointer-events:all;
        position:relative;overflow:hidden;
        transform:translateX(120%);opacity:0;
        transition:transform .3s cubic-bezier(.175,.885,.32,1.2),opacity .3s;
        border-left:4px solid transparent;
      }
      .sipro-toast.show{transform:translateX(0);opacity:1;}
      .sipro-toast.hide{transform:translateX(120%);opacity:0;transition:transform .25s ease-in,opacity .25s;}
      .sipro-toast-icon{width:20px;height:20px;flex-shrink:0;margin-top:1px;}
      .sipro-toast-body{flex:1;min-width:0;}
      .sipro-toast-title{font-size:.8rem;font-weight:700;margin-bottom:2px;text-transform:uppercase;letter-spacing:.4px;}
      .sipro-toast-msg{font-size:.875rem;color:#374151;line-height:1.45;word-break:break-word;}
      .sipro-toast-close{background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1rem;line-height:1;padding:0;flex-shrink:0;margin-top:1px;}
      .sipro-toast-close:hover{color:#374151;}
      /* progress bar */
      .sipro-toast-bar{
        position:absolute;bottom:0;left:0;height:3px;width:100%;
        transform-origin:left;animation:sipro-bar var(--dur,4s) linear forwards;
      }
      @keyframes sipro-bar{from{transform:scaleX(1);}to{transform:scaleX(0);}}
      /* types */
      .sipro-toast.t-success{border-color:#22c55e;}
      .sipro-toast.t-success .sipro-toast-title{color:#16a34a;}
      .sipro-toast.t-success .sipro-toast-bar{background:#22c55e;}
      .sipro-toast.t-error{border-color:#ef4444;}
      .sipro-toast.t-error .sipro-toast-title{color:#dc2626;}
      .sipro-toast.t-error .sipro-toast-bar{background:#ef4444;}
      .sipro-toast.t-warning{border-color:#f59e0b;}
      .sipro-toast.t-warning .sipro-toast-title{color:#d97706;}
      .sipro-toast.t-warning .sipro-toast-bar{background:#f59e0b;}
      .sipro-toast.t-info{border-color:#3b82f6;}
      .sipro-toast.t-info .sipro-toast-title{color:#2563eb;}
      .sipro-toast.t-info .sipro-toast-bar{background:#3b82f6;}
    </style>

    <script>
    /* ── Modal ── */
    function openSiproModal(id){document.getElementById(id).classList.add('is-open');}
    function closeSiproModal(id){document.getElementById(id).classList.remove('is-open');}
    document.addEventListener('keydown',function(e){
      if(e.key==='Escape'){
        document.querySelectorAll('.sipro-overlay.is-open').forEach(function(m){m.classList.remove('is-open');});
      }
    });

    /* ── Confirm dialog ──
       Pakai: <button data-confirm="Pesan?" data-form="form-id" data-confirm-type="danger" data-confirm-title="Judul">
       atau JS: siproConfirm({ message, formId, type, title })
    ── */
    var _confirmCallback = null;
    function siproConfirm(opts){
      var type    = opts.type    || 'danger';
      var title   = opts.title   || (type === 'danger' ? 'Hapus Data' : 'Konfirmasi');
      var message = opts.message || 'Apakah Anda yakin?';
      var icons   = {
        danger:  '<svg style="width:18px;height:18px;fill:#ef4444" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>',
        warning: '<svg style="width:18px;height:18px;fill:#f59e0b" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
        primary: '<svg style="width:18px;height:18px;fill:#3b82f6" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
      };
      var btnColors = { danger:'btn-danger', warning:'btn-warning', primary:'btn-primary' };
      var btnLabels = { danger:'Ya, Hapus', warning:'Ya, Lanjutkan', primary:'Ya, Kirim' };

      document.getElementById('sipro-confirm-icon').innerHTML        = icons[type]  || icons.primary;
      document.getElementById('sipro-confirm-title-text').textContent = title;
      document.getElementById('sipro-confirm-message').textContent    = message;

      var btn = document.getElementById('sipro-confirm-btn');
      btn.className = 'btn btn-sm ' + (btnColors[type] || 'btn-primary');
      btn.textContent = opts.confirmText || btnLabels[type] || 'Konfirmasi';

      _confirmCallback = opts.onConfirm || null;
      openSiproModal('sipro-confirm-overlay');
    }

    document.getElementById('sipro-confirm-btn').addEventListener('click', function(){
      closeSiproModal('sipro-confirm-overlay');
      if(_confirmCallback){ _confirmCallback(); _confirmCallback = null; }
    });

    /* Global listener: tombol dengan data-confirm akan otomatis pakai confirm modal */
    document.addEventListener('click', function(e){
      var btn = e.target.closest('[data-confirm]');
      if(!btn) return;
      e.preventDefault();
      var formId = btn.getAttribute('data-form');
      siproConfirm({
        message:     btn.getAttribute('data-confirm'),
        title:       btn.getAttribute('data-confirm-title') || undefined,
        type:        btn.getAttribute('data-confirm-type')  || 'danger',
        confirmText: btn.getAttribute('data-confirm-ok')    || undefined,
        onConfirm:   function(){
          if(formId) document.getElementById(formId).submit();
        }
      });
    });

    /* ── Client-side validation bootstrap integration ── */
    (function(){
      document.addEventListener('submit', function(e){
        var form = e.target;
        if(form.hasAttribute('data-no-validate')) return;
        if(!form.checkValidity()){
          e.preventDefault();
          e.stopPropagation();
          siproLoading.hide();
          form.querySelectorAll(':invalid').forEach(function(el){
            el.classList.add('is-invalid');
            el.addEventListener('input', function(){ el.classList.remove('is-invalid'); }, {once:true});
          });
          var first = form.querySelector(':invalid');
          if(first) first.focus();
        }
        form.classList.add('was-validated');
      }, true);
    })();

    /* ── Loading ── */
    var siproLoading = {
      el: null,
      init: function(){ this.el = document.getElementById('sipro-loading'); },
      show: function(msg){
        if(!this.el) this.init();
        var t = this.el.querySelector('.sipro-loading-text');
        if(t) t.textContent = msg || 'Memproses...';
        this.el.classList.add('is-active');
      },
      hide: function(){
        if(!this.el) this.init();
        this.el.classList.remove('is-active');
      }
    };
    siproLoading.init();

    /* Auto-trigger loading on all form submits (skip GET forms & forms with data-no-loading) */
    document.addEventListener('submit', function(e){
      var form = e.target;
      if(form.method && form.method.toLowerCase() === 'get') return;
      if(form.hasAttribute('data-no-loading')) return;
      var msg = form.getAttribute('data-loading-msg') || 'Menyimpan...';
      siproLoading.show(msg);
    });

    /* ── Toast ── */
    var siproToast = (function(){
      var ICONS = {
        success: '<svg viewBox="0 0 24 24" fill="#22c55e"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
        error:   '<svg viewBox="0 0 24 24" fill="#ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="#f59e0b"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
        info:    '<svg viewBox="0 0 24 24" fill="#3b82f6"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>',
      };
      var TITLES = { success:'Berhasil', error:'Gagal', warning:'Perhatian', info:'Info' };
      var container;

      function show(msg, type, dur){
        if(!container) container = document.getElementById('sipro-toasts');
        type = type || 'info';
        dur  = (dur || 4) * 1000;

        var el = document.createElement('div');
        el.className = 'sipro-toast t-' + type;
        el.style.setProperty('--dur', (dur/1000) + 's');
        el.innerHTML =
          '<span class="sipro-toast-icon">' + (ICONS[type]||ICONS.info) + '</span>' +
          '<div class="sipro-toast-body">' +
            '<div class="sipro-toast-title">' + TITLES[type] + '</div>' +
            '<div class="sipro-toast-msg">' + msg + '</div>' +
          '</div>' +
          '<button class="sipro-toast-close" aria-label="Tutup">&times;</button>' +
          '<div class="sipro-toast-bar"></div>';

        container.appendChild(el);
        requestAnimationFrame(function(){ requestAnimationFrame(function(){ el.classList.add('show'); }); });

        var timer = setTimeout(function(){ dismiss(el); }, dur);

        el.querySelector('.sipro-toast-close').addEventListener('click', function(){
          clearTimeout(timer);
          dismiss(el);
        });
      }

      function dismiss(el){
        el.classList.add('hide');
        el.addEventListener('transitionend', function(){ el.remove(); }, {once:true});
      }

      return show;
    })();
    </script>

    {{-- Flash session → toast (fired after DOM + scripts ready) --}}
    @if(session('status'))
    <script>document.addEventListener('DOMContentLoaded',function(){siproToast(@json(session('status')),'success');});</script>
    @endif
    @if(session('error'))
    <script>document.addEventListener('DOMContentLoaded',function(){siproToast(@json(session('error')),'error');});</script>
    @endif
    @if(session('warning'))
    <script>document.addEventListener('DOMContentLoaded',function(){siproToast(@json(session('warning')),'warning');});</script>
    @endif
    @if(session('info'))
    <script>document.addEventListener('DOMContentLoaded',function(){siproToast(@json(session('info')),'info');});</script>
    @endif

  </body>
</html>
