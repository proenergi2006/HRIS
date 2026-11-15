<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Formulir Lamaran Kerja ProEnergi</title>
<meta name="description" content="Bergabung bersama ProEnergi. Isi formulir lamaran kerja online berikut dan tim HRD kami akan menghubungi Anda.">
<link rel="icon" type="image/png" href="{{ asset('img/propeople-icon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --navy:#0b2447; --navy2:#19376d; --navy3:#27498c;
    --accent:#f2a900; --accent2:#ffc83d;
    --text:#1b2638; --muted:#64748b; --line:#dbe3ee; --soft:#f4f7fb; --bg:#eef2f8;
    --danger:#d92d20; --ok:#12805c;
    --r:14px;
  }
  html { scroll-behavior:smooth; }
  body { font-family:'Inter',system-ui,sans-serif; background:var(--bg); color:var(--text); line-height:1.5; -webkit-font-smoothing:antialiased; }

  /* ── Hero ── */
  .hero { position:relative; overflow:hidden; color:#fff; text-align:center; padding:44px 20px 112px;
    background:radial-gradient(1200px 400px at 85% -10%, rgba(242,169,0,.22), transparent 60%),
               radial-gradient(900px 500px at -10% 110%, rgba(39,73,140,.9), transparent 60%),
               linear-gradient(135deg,var(--navy),var(--navy2)); }
  .hero::after { content:''; position:absolute; inset:0; opacity:.5; pointer-events:none;
    background-image:radial-gradient(rgba(255,255,255,.09) 1px, transparent 1px); background-size:22px 22px; }
  .hero > * { position:relative; z-index:1; }
  .logo { display:inline-flex; align-items:center; justify-content:center; background:#fff; border-radius:14px; padding:10px 18px; margin-bottom:22px; box-shadow:0 8px 28px rgba(0,0,0,.25); }
  .logo img { height:34px; display:block; }
  .badge { display:inline-block; font-size:.72rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase;
    color:var(--accent2); border:1px solid rgba(255,200,61,.4); background:rgba(255,200,61,.1); padding:5px 14px; border-radius:99px; margin-bottom:14px; }
  .hero h1 { font-size:clamp(1.7rem,4.6vw,2.6rem); font-weight:800; letter-spacing:-.02em; line-height:1.15; }
  .hero h1 span { color:var(--accent2); }
  .hero p.lead { max-width:560px; margin:12px auto 0; color:rgba(255,255,255,.78); font-size:1rem; }
  .chips { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin-top:22px; }
  .chip { display:inline-flex; align-items:center; gap:7px; font-size:.78rem; font-weight:600; color:#fff;
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); padding:7px 13px; border-radius:99px; backdrop-filter:blur(4px); }
  .chip svg { width:15px; height:15px; stroke:var(--accent2); }

  /* ── Wrapper & stepper ── */
  .wrap { max-width:880px; margin:-72px auto 48px; padding:0 16px; position:relative; z-index:2; }
  .stepper { background:#fff; border-radius:var(--r); box-shadow:0 10px 40px rgba(11,36,71,.14); padding:18px 22px 20px; margin-bottom:18px; }
  .steps { display:flex; justify-content:space-between; position:relative; }
  .steps::before { content:''; position:absolute; top:18px; left:8%; right:8%; height:3px; background:var(--line); border-radius:3px; }
  .steps .fill { position:absolute; top:18px; left:8%; height:3px; background:linear-gradient(90deg,var(--accent),var(--accent2)); border-radius:3px; width:0; transition:width .4s ease; }
  .step { flex:1; text-align:center; position:relative; z-index:1; cursor:default; }
  .step .dot { width:38px; height:38px; margin:0 auto 7px; border-radius:50%; display:flex; align-items:center; justify-content:center;
    background:#fff; border:3px solid var(--line); color:var(--muted); font-weight:700; font-size:.88rem; transition:all .25s; }
  .step .lbl { font-size:.72rem; font-weight:600; color:var(--muted); transition:color .25s; }
  .step.active .dot { border-color:var(--navy2); background:var(--navy2); color:#fff; box-shadow:0 0 0 5px rgba(25,55,109,.14); }
  .step.active .lbl { color:var(--navy); }
  .step.done .dot { border-color:var(--accent); background:var(--accent); color:var(--navy); }
  .step.done .dot span { display:none; }
  .step.done .dot::after { content:'✓'; font-weight:800; }
  .step.done { cursor:pointer; }

  /* ── Card & panels ── */
  .panel { display:none; animation:rise .4s ease; }
  .panel.on { display:block; }
  @keyframes rise { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:none; } }
  .card { background:#fff; border-radius:var(--r); box-shadow:0 6px 28px rgba(11,36,71,.08); padding:26px; margin-bottom:16px; }
  .card h2 { display:flex; align-items:center; gap:11px; font-size:1.05rem; font-weight:700; color:var(--navy); margin-bottom:4px; }
  .card h2 .ico { width:34px; height:34px; border-radius:10px; background:var(--soft); display:flex; align-items:center; justify-content:center; flex:none; }
  .card h2 .ico svg { width:18px; height:18px; stroke:var(--navy2); }
  .card .sub { color:var(--muted); font-size:.82rem; margin:0 0 18px 45px; }
  .grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
  .full { grid-column:1/-1; }

  label { display:block; font-size:.78rem; font-weight:600; color:#2c3a52; margin-bottom:5px; }
  label small { font-weight:400; color:var(--muted); }
  label.req::after { content:' *'; color:var(--danger); }
  input, select, textarea { width:100%; padding:11px 13px; border:1.5px solid var(--line); border-radius:10px; font:inherit; font-size:.92rem; color:var(--text); background:#fff; transition:border .15s, box-shadow .15s; }
  input:hover, select:hover, textarea:hover { border-color:#b9c6d8; }
  input:focus, select:focus, textarea:focus { outline:none; border-color:var(--navy3); box-shadow:0 0 0 4px rgba(39,73,140,.14); }
  input.bad, select.bad, textarea.bad { border-color:var(--danger); background:#fff8f7; }
  .err { color:var(--danger); font-size:.75rem; margin-top:4px; font-weight:500; }

  .row-card { position:relative; border:1.5px solid var(--line); border-radius:12px; padding:18px 16px 16px; margin-bottom:14px; background:var(--soft); }
  .row-card .tag { position:absolute; top:-10px; left:14px; background:var(--navy2); color:#fff; font-size:.68rem; font-weight:700; padding:2px 10px; border-radius:99px; letter-spacing:.04em; }
  .btn-rm { position:absolute; top:8px; right:10px; background:none; border:0; color:var(--muted); font-size:.75rem; font-weight:600; cursor:pointer; padding:4px 8px; border-radius:6px; }
  .btn-rm:hover { background:#fee4e2; color:var(--danger); }
  .btn { border:0; border-radius:11px; padding:12px 22px; font:inherit; font-size:.92rem; font-weight:700; cursor:pointer; transition:transform .12s, box-shadow .2s, background .2s; }
  .btn:active { transform:scale(.98); }
  .btn-add { width:100%; background:#fff; border:2px dashed var(--navy3); color:var(--navy3); }
  .btn-add:hover { background:#f0f5ff; }
  .btn-next { background:linear-gradient(135deg,var(--accent),var(--accent2)); color:var(--navy); box-shadow:0 6px 18px rgba(242,169,0,.35); }
  .btn-next:hover { box-shadow:0 8px 24px rgba(242,169,0,.5); }
  .btn-back { background:#fff; color:var(--navy2); border:1.5px solid var(--line); }
  .btn-back:hover { background:var(--soft); }
  .btn-submit { background:linear-gradient(135deg,var(--navy),var(--navy3)); color:#fff; box-shadow:0 6px 18px rgba(11,36,71,.35); }
  .btn[disabled] { opacity:.65; cursor:wait; }
  .nav { display:flex; justify-content:space-between; gap:12px; margin-top:6px; }
  .nav .btn { min-width:130px; }

  /* ── Upload ── */
  .drop { position:relative; border:2px dashed var(--line); border-radius:12px; background:var(--soft); padding:20px 14px; text-align:center; transition:all .2s; cursor:pointer; }
  .drop:hover, .drop.over { border-color:var(--navy3); background:#eef4ff; }
  .drop input { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; padding:0; }
  .drop svg { width:28px; height:28px; stroke:var(--navy3); margin-bottom:6px; }
  .drop b { display:block; font-size:.85rem; color:var(--navy); }
  .drop span { font-size:.72rem; color:var(--muted); }
  .drop.has { border-style:solid; border-color:var(--ok); background:#ecfdf3; }
  .drop.has b { color:var(--ok); word-break:break-all; }

  .check { display:flex; gap:12px; align-items:flex-start; font-size:.88rem; background:var(--soft); border:1.5px solid var(--line); border-radius:12px; padding:14px 16px; cursor:pointer; }
  .check input { width:20px; height:20px; flex:none; margin-top:1px; accent-color:var(--navy2); padding:0; }
  .secure { display:flex; gap:10px; align-items:center; font-size:.78rem; color:var(--muted); margin-top:14px; }
  .secure svg { width:16px; height:16px; stroke:var(--ok); flex:none; }
  .alert { background:#fef3f2; border:1px solid #fecdca; color:#b42318; border-radius:12px; padding:13px 16px; font-size:.86rem; margin-bottom:16px; }
  .hp { position:absolute; left:-9999px; }
  footer { text-align:center; color:var(--muted); font-size:.75rem; padding:0 16px 32px; }


  /* ── Modal petunjuk ── */
  .modal { position:fixed; inset:0; z-index:100; display:none; align-items:center; justify-content:center; padding:16px; background:rgba(11,36,71,.65); backdrop-filter:blur(3px); }
  .modal.on { display:flex; animation:fade .25s ease; }
  @keyframes fade { from { opacity:0; } to { opacity:1; } }
  .dlg { background:#fff; border-radius:20px; width:100%; max-width:560px; max-height:calc(100vh - 32px); overflow:auto; box-shadow:0 24px 70px rgba(0,0,0,.4); animation:pop .35s cubic-bezier(.2,1.2,.3,1); }
  @keyframes pop { from { opacity:0; transform:translateY(18px) scale(.96); } to { opacity:1; transform:none; } }
  .dlg-head { background:linear-gradient(135deg,var(--navy),var(--navy2)); color:#fff; padding:24px 26px 20px; border-radius:20px 20px 0 0; }
  .dlg-head h3 { font-size:1.2rem; font-weight:800; }
  .dlg-head p { font-size:.85rem; color:rgba(255,255,255,.78); margin-top:4px; }
  .dlg-body { padding:22px 26px 6px; }
  .how { list-style:none; counter-reset:n; }
  .how li { counter-increment:n; display:flex; gap:13px; margin-bottom:15px; }
  .how li::before { content:counter(n); flex:none; width:28px; height:28px; border-radius:50%; background:var(--accent); color:var(--navy); font-weight:800; font-size:.85rem; display:flex; align-items:center; justify-content:center; }
  .how b { display:block; font-size:.9rem; color:var(--navy); }
  .how span { font-size:.82rem; color:var(--muted); }
  .prep { background:var(--soft); border:1.5px solid var(--line); border-radius:12px; padding:13px 16px; margin:4px 0 6px; font-size:.82rem; color:#334155; }
  .prep b { color:var(--navy); }
  .dlg-foot { padding:14px 26px 24px; display:flex; justify-content:flex-end; }
  .dlg-foot .btn { width:100%; }
  .help { background:none; border:0; color:rgba(255,255,255,.85); font:inherit; font-size:.8rem; font-weight:600; text-decoration:underline; cursor:pointer; margin-top:16px; }
  .help:hover { color:#fff; }

  @media (max-width:680px) {
    .grid { grid-template-columns:1fr; }
    .card { padding:20px 16px; }
    .card .sub { margin-left:0; }
    .step .lbl { font-size:.62rem; }
    .step .dot { width:32px; height:32px; }
    .steps::before, .steps .fill { top:15px; }
    .hero { padding-bottom:104px; }
    .nav .btn { min-width:0; flex:1; }
  }
</style>
</head>
<body>

<header class="hero">
  <div class="logo"><img src="{{ asset('img/logo-pe.png') }}" alt="ProEnergi" onerror="this.parentNode.style.display='none'"></div>
  <div class="badge">Karir di ProEnergi</div>
  <h1>Formulir Lamaran Kerja</h1>
  <p class="lead">Tertarik bergabung? Lengkapi formulir ini dan tim HRD kami akan menghubungi Anda untuk proses selanjutnya.</p>
  <div class="chips">
    <span class="chip"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>± 10 menit</span>
    <span class="chip"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>Data KTP & rekening terenkripsi</span>
    <span class="chip"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>4 langkah mudah</span>
  </div>
  <button type="button" class="help" id="guideOpen">Lihat tata cara pengisian</button>
</header>


<div class="modal" id="guide" role="dialog" aria-modal="true" aria-labelledby="guideTitle">
  <div class="dlg">
    <div class="dlg-head">
      <h3 id="guideTitle">Tata Cara Mengisi Formulir</h3>
      <p>Baca sebentar supaya proses pengisian lebih lancar.</p>
    </div>
    <div class="dlg-body">
      <ol class="how">
        <li><div><b>Data Diri</b><span>Pilih lowongan atau tulis posisi yang dilamar, lalu isi data pribadi dan alamat sesuai KTP.</span></div></li>
        <li><div><b>Pendidikan &amp; Karir</b><span>Isi riwayat pendidikan, pengalaman kerja, dan keahlian. Klik "+ Tambah" untuk menambah baris.</span></div></li>
        <li><div><b>Kontak &amp; Rekening</b><span>Isi kontak darurat. Rekening dan BPJS bersifat opsional.</span></div></li>
        <li><div><b>Dokumen</b><span>Unggah CV, KTP, dan ijazah, lalu centang pernyataan dan klik <b style="display:inline">Kirim Lamaran</b>.</span></div></li>
      </ol>
      <div class="prep">
        <b>Siapkan sebelum mulai:</b> No. KTP (16 digit), CV, foto/scan KTP, dan ijazah terakhir (PDF/JPG/PNG, maks 5 MB per file).<br>
        Kolom bertanda <b style="color:var(--danger)">*</b> wajib diisi. Gunakan email dan nomor WhatsApp yang aktif agar kami mudah menghubungi Anda.
      </div>
    </div>
    <div class="dlg-foot"><button type="button" class="btn btn-next" id="guideClose">Mengerti, Mulai Mengisi</button></div>
  </div>
</div>

<main class="wrap">
  <div class="stepper">
    <div class="steps" id="steps">
      <div class="fill" id="fill"></div>
      <div class="step" data-s="1"><div class="dot"><span>1</span></div><div class="lbl">Data Diri</div></div>
      <div class="step" data-s="2"><div class="dot"><span>2</span></div><div class="lbl">Pendidikan & Karir</div></div>
      <div class="step" data-s="3"><div class="dot"><span>3</span></div><div class="lbl">Kontak & Rekening</div></div>
      <div class="step" data-s="4"><div class="dot"><span>4</span></div><div class="lbl">Dokumen</div></div>
    </div>
  </div>

  <form method="POST" action="{{ route('lamaran.store') }}" enctype="multipart/form-data" id="applyForm" novalidate>
    @csrf
    <div class="hp"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

    @if($errors->any())
      <div class="alert"><strong>Formulir belum lengkap.</strong> Periksa kolom yang ditandai merah — kami sudah membawa Anda ke langkah yang perlu diperbaiki.</div>
    @endif

    @php
      $ic = [
        'brief'  => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/>',
        'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/>',
        'pin'    => '<path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'cap'    => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/>',
        'work'   => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M3 13h18"/>',
        'star'   => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
        'phone'  => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>',
        'bank'   => '<path d="M3 10l9-6 9 6M5 10v8M9 10v8M15 10v8M19 10v8M3 21h18"/>',
        'file'   => '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5"/>',
      ];
      $icon = fn ($k) => '<span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'.$ic[$k].'</svg></span>';

      $f = function ($name, $label, $opts = []) use ($errors) {
        $type = $opts['type'] ?? 'text'; $req = !empty($opts['required']); $hint = $opts['hint'] ?? '';
        $cls = $opts['class'] ?? '';
        echo '<div class="'.$cls.'"><label for="'.$name.'" class="'.($req?'req':'').'">'.e($label).($hint?' <small>'.e($hint).'</small>':'').'</label>';
        echo '<input type="'.$type.'" id="'.$name.'" name="'.$name.'" value="'.e(old($name)).'"'.($req?' data-req="1"':'').(!empty($opts['inputmode'])?' inputmode="'.$opts['inputmode'].'"':'').(!empty($opts['maxlength'])?' maxlength="'.$opts['maxlength'].'"':'').(!empty($opts['ph'])?' placeholder="'.e($opts['ph']).'"':'').($errors->has($name)?' class="bad"':'').'>';
        if ($errors->has($name)) echo '<div class="err">'.e($errors->first($name)).'</div>';
        echo '</div>';
      };
    @endphp

    {{-- ══ LANGKAH 1 ══ --}}
    <section class="panel" data-p="1">
      <div class="card">
        <h2>{!! $icon('brief') !!} Posisi yang Dilamar</h2>
        <p class="sub">Pilih lowongan yang tersedia, atau tuliskan posisi yang Anda minati.</p>
        <div class="grid">
          <div class="full">
            <label for="job_requisition_id">Lowongan Tersedia</label>
            <select id="job_requisition_id" name="job_requisition_id">
              <option value="">-- Pilih lowongan (atau isi posisi di bawah) --</option>
              @foreach($requisitions as $r)
                <option value="{{ $r->id }}" {{ old('job_requisition_id') == $r->id ? 'selected' : '' }}>
                  {{ $r->title }}@if($r->company) — {{ $r->company->short_name ?? $r->company->name }}@endif
                </option>
              @endforeach
            </select>
            @error('job_requisition_id')<div class="err">{{ $message }}</div>@enderror
          </div>
          @php $f('applied_position', 'Posisi yang Dilamar', ['hint' => '(isi jika tidak ada di daftar)', 'class' => 'full']); @endphp
          @php $f('expected_salary', 'Ekspektasi Gaji (Rp)', ['inputmode' => 'numeric', 'ph' => 'mis. 7000000']); @endphp
        </div>
      </div>

      <div class="card">
        <h2>{!! $icon('user') !!} Data Pribadi</h2>
        <p class="sub">Isi sesuai KTP.</p>
        <div class="grid">
          @php $f('name', 'Nama Lengkap', ['required' => true, 'class' => 'full']); @endphp
          @php $f('email', 'Email', ['required' => true, 'type' => 'email', 'ph' => 'nama@email.com']); @endphp
          @php $f('phone', 'No. HP / WhatsApp', ['required' => true, 'inputmode' => 'tel', 'ph' => '08xxxxxxxxxx']); @endphp
          <div>
            <label for="gender" class="req">Jenis Kelamin</label>
            <select id="gender" name="gender" data-req="1" class="{{ $errors->has('gender') ? 'bad' : '' }}">
              <option value="">-- Pilih --</option>
              <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
              <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @error('gender')<div class="err">{{ $message }}</div>@enderror
          </div>
          @php $f('birth_place', 'Tempat Lahir', ['required' => true]); @endphp
          @php $f('birth_date', 'Tanggal Lahir', ['required' => true, 'type' => 'date']); @endphp
          <div>
            <label for="marital_status_id">Status Pernikahan</label>
            <select id="marital_status_id" name="marital_status_id"><option value="">-- Pilih --</option>
              @foreach($maritalStatuses as $m)<option value="{{ $m->id }}" {{ old('marital_status_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>@endforeach
            </select>
          </div>
          <div>
            <label for="religion_id">Agama</label>
            <select id="religion_id" name="religion_id"><option value="">-- Pilih --</option>
              @foreach($religions as $m)<option value="{{ $m->id }}" {{ old('religion_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>@endforeach
            </select>
          </div>
          <div>
            <label for="blood_type_id">Golongan Darah</label>
            <select id="blood_type_id" name="blood_type_id"><option value="">-- Pilih --</option>
              @foreach($bloodTypes as $m)<option value="{{ $m->id }}" {{ old('blood_type_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>@endforeach
            </select>
          </div>
          @php $f('ktp_number', 'No. KTP', ['required' => true, 'inputmode' => 'numeric', 'maxlength' => 16, 'hint' => '(16 digit)']); @endphp
          @php $f('npwp_number', 'NPWP', ['hint' => '(opsional)', 'inputmode' => 'numeric']); @endphp
        </div>
      </div>

      <div class="card">
        <h2>{!! $icon('pin') !!} Alamat</h2>
        <p class="sub">Alamat domisili boleh dikosongkan jika sama dengan KTP.</p>
        <div class="grid">
          @php $f('ktp_address', 'Alamat Sesuai KTP', ['required' => true, 'class' => 'full']); @endphp
          @php $f('ktp_city', 'Kota Sesuai KTP', ['required' => true]); @endphp
          <div></div>
          @php $f('domicile_address', 'Alamat Domisili', ['class' => 'full']); @endphp
          @php $f('domicile_city', 'Kota Domisili'); @endphp
        </div>
      </div>
      <div class="nav"><span></span><button type="button" class="btn btn-next" data-next>Lanjut →</button></div>
    </section>

    {{-- ══ LANGKAH 2 ══ --}}
    <section class="panel" data-p="2">
      <div class="card">
        <h2>{!! $icon('cap') !!} Pendidikan</h2>
        <p class="sub">Mulai dari pendidikan terakhir.</p>
        <div id="eduList"></div>
        <button type="button" class="btn btn-add" id="addEdu">+ Tambah Pendidikan</button>
      </div>
      <div class="card">
        <h2>{!! $icon('work') !!} Pengalaman Kerja</h2>
        <p class="sub">Lewati jika Anda fresh graduate.</p>
        <div id="expList"></div>
        <button type="button" class="btn btn-add" id="addExp">+ Tambah Pengalaman</button>
      </div>
      <div class="card">
        <h2>{!! $icon('star') !!} Keahlian</h2>
        <p class="sub">Tuliskan keahlian utama Anda, pisahkan dengan koma.</p>
        <textarea id="skills" name="skills" rows="2" placeholder="mis. Microsoft Excel, Negosiasi, Bahasa Inggris">{{ old('skills') }}</textarea>
      </div>
      <div class="nav"><button type="button" class="btn btn-back" data-prev>← Kembali</button><button type="button" class="btn btn-next" data-next>Lanjut →</button></div>
    </section>

    {{-- ══ LANGKAH 3 ══ --}}
    <section class="panel" data-p="3">
      <div class="card">
        <h2>{!! $icon('phone') !!} Kontak Darurat</h2>
        <p class="sub">Orang yang dapat kami hubungi dalam keadaan darurat.</p>
        <div class="grid">
          @php $f('emergency_contact_name', 'Nama', ['required' => true]); @endphp
          @php $f('emergency_contact_relation', 'Hubungan', ['required' => true, 'ph' => 'mis. Ayah, Istri']); @endphp
          @php $f('emergency_contact_phone', 'No. HP', ['required' => true, 'inputmode' => 'tel']); @endphp
        </div>
      </div>
      <div class="card">
        <h2>{!! $icon('bank') !!} Rekening & BPJS</h2>
        <p class="sub">Opsional — bisa dilengkapi nanti saat proses penerimaan.</p>
        <div class="grid">
          <div>
            <label for="bank_id">Bank</label>
            <select id="bank_id" name="bank_id"><option value="">-- Pilih --</option>
              @foreach($banks as $b)<option value="{{ $b->id }}" {{ old('bank_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach
            </select>
          </div>
          @php $f('bank_account_number', 'No. Rekening', ['inputmode' => 'numeric']); @endphp
          @php $f('bank_account_holder', 'Nama Pemilik Rekening', ['class' => 'full']); @endphp
          @php $f('bpjs_health_number', 'No. BPJS Kesehatan'); @endphp
          @php $f('bpjs_employment_number', 'No. BPJS Ketenagakerjaan'); @endphp
        </div>
      </div>
      <div class="nav"><button type="button" class="btn btn-back" data-prev>← Kembali</button><button type="button" class="btn btn-next" data-next>Lanjut →</button></div>
    </section>

    {{-- ══ LANGKAH 4 ══ --}}
    <section class="panel" data-p="4">
      <div class="card">
        <h2>{!! $icon('file') !!} Dokumen Pendukung</h2>
        <p class="sub">PDF, JPG, atau PNG — maksimal 5 MB per file. Klik atau seret file ke kotak.</p>
        <div class="grid">
          @foreach([
            ['doc_cv', 'CV / Resume', '.pdf,.doc,.docx', 'PDF / DOC'],
            ['doc_ktp', 'KTP', '.pdf,.jpg,.jpeg,.png', 'PDF / JPG / PNG'],
            ['doc_ijazah', 'Ijazah Terakhir', '.pdf,.jpg,.jpeg,.png', 'PDF / JPG / PNG'],
          ] as [$n, $t, $acc, $hint])
            <div>
              <label>{{ $t }}</label>
              <div class="drop" data-drop>
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v3a1 1 0 001 1h14a1 1 0 001-1v-3"/></svg>
                <b data-name>Pilih file</b><span data-hint>{{ $hint }}</span>
                <input type="file" name="{{ $n }}" accept="{{ $acc }}">
              </div>
              @error($n)<div class="err">{{ $message }}</div>@enderror
            </div>
          @endforeach
        </div>
      </div>

      <div class="card">
        <label class="check">
          <input type="checkbox" name="declaration" value="1" {{ old('declaration') ? 'checked' : '' }}>
          <span>Saya menyatakan bahwa seluruh data yang saya isi adalah benar, dan bersedia datanya diproses oleh ProEnergi untuk keperluan rekrutmen.</span>
        </label>
        @error('declaration')<div class="err">{{ $message }}</div>@enderror
        <div class="secure">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg>
          Data Anda hanya digunakan untuk proses rekrutmen. No. KTP, NPWP, dan rekening disimpan terenkripsi.
        </div>
      </div>
      <div class="nav"><button type="button" class="btn btn-back" data-prev>← Kembali</button><button type="submit" class="btn btn-submit" id="submitBtn">Kirim Lamaran ✓</button></div>
    </section>
  </form>
</main>
<footer>© {{ date('Y') }} ProEnergi · Formulir Lamaran Kerja</footer>

<script>
(function () {
  var form = document.getElementById('applyForm');
  var panels = [].slice.call(document.querySelectorAll('.panel'));
  var steps  = [].slice.call(document.querySelectorAll('.step'));
  var fill   = document.getElementById('fill');
  var cur = 1, total = panels.length;

  function show(n) {
    cur = n;
    panels.forEach(function (p) { p.classList.toggle('on', +p.dataset.p === n); });
    steps.forEach(function (s) {
      var i = +s.dataset.s;
      s.classList.toggle('active', i === n);
      s.classList.toggle('done', i < n);
    });
    fill.style.width = ((n - 1) / (total - 1) * 84) + '%';
    window.scrollTo({ top: document.querySelector('.stepper').offsetTop - 12, behavior: 'smooth' });
  }

  // Validasi ringan per langkah (kolom bertanda data-req). Validasi penuh tetap di server.
  function validStep(n) {
    var ok = true, first = null;
    [].forEach.call(document.querySelector('.panel[data-p="' + n + '"]').querySelectorAll('[data-req]'), function (el) {
      var bad = !el.value.trim();
      if (el.id === 'ktp_number' && el.value && !/^\d{16}$/.test(el.value.replace(/\D/g, ''))) bad = true;
      if (el.type === 'email' && el.value && !/^\S+@\S+\.\S+$/.test(el.value)) bad = true;
      el.classList.toggle('bad', bad);
      if (bad) { ok = false; first = first || el; }
    });
    if (n === 1 && !document.getElementById('job_requisition_id').value && !document.getElementById('applied_position').value.trim()) {
      var ap = document.getElementById('applied_position'); ap.classList.add('bad'); ok = false; first = first || ap;
    }
    if (first) first.focus({ preventScroll: false });
    return ok;
  }

  document.addEventListener('input', function (e) { if (e.target.classList) e.target.classList.remove('bad'); });
  [].forEach.call(document.querySelectorAll('[data-next]'), function (b) { b.onclick = function () { if (validStep(cur)) show(cur + 1); }; });
  [].forEach.call(document.querySelectorAll('[data-prev]'), function (b) { b.onclick = function () { show(cur - 1); }; });
  steps.forEach(function (s) { s.onclick = function () { if (+s.dataset.s < cur) show(+s.dataset.s); }; });

  // Kirim: cek langkah 1 & 3 dulu, tampilkan status loading.
  form.addEventListener('submit', function (e) {
    for (var n = 1; n <= 3; n++) { if (!validStep(n)) { e.preventDefault(); show(n); return; } }
    var b = document.getElementById('submitBtn'); b.disabled = true; b.textContent = 'Mengirim…';
  });

  // Upload: tampilkan nama file + drag & drop
  [].forEach.call(document.querySelectorAll('[data-drop]'), function (d) {
    var input = d.querySelector('input'), name = d.querySelector('[data-name]');
    input.addEventListener('change', function () {
      var f = input.files[0];
      d.classList.toggle('has', !!f);
      name.textContent = f ? f.name : 'Pilih file';
    });
    ['dragenter', 'dragover'].forEach(function (ev) { d.addEventListener(ev, function () { d.classList.add('over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { d.addEventListener(ev, function () { d.classList.remove('over'); }); });
  });

  // Baris dinamis pendidikan & pengalaman
  var eduLevels = @json($eduLevels->pluck('name'));
  var oldEdu = @json(old('educations', [])), oldExp = @json(old('experiences', []));
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  var ei = 0, xi = 0;

  function addEdu(v) {
    v = v || {}; var i = ei++;
    var opts = '<option value="">-- Pilih --</option>' + eduLevels.map(function (n) {
      return '<option' + (v.education_level === n ? ' selected' : '') + '>' + esc(n) + '</option>'; }).join('');
    var d = document.createElement('div'); d.className = 'row-card';
    d.innerHTML = '<span class="tag">Pendidikan</span><button type="button" class="btn-rm">Hapus</button><div class="grid">' +
      '<div><label>Jenjang</label><select name="educations[' + i + '][education_level]">' + opts + '</select></div>' +
      '<div><label>Jurusan</label><input name="educations[' + i + '][major]" value="' + esc(v.major) + '"></div>' +
      '<div class="full"><label>Institusi</label><input name="educations[' + i + '][institution]" value="' + esc(v.institution) + '"></div>' +
      '<div><label>Tahun Lulus</label><input name="educations[' + i + '][graduation_year]" inputmode="numeric" maxlength="4" value="' + esc(v.graduation_year) + '"></div>' +
      '<div><label>IPK / Nilai</label><input name="educations[' + i + '][gpa]" inputmode="decimal" value="' + esc(v.gpa) + '"></div></div>';
    d.querySelector('.btn-rm').onclick = function () { d.remove(); };
    document.getElementById('eduList').appendChild(d);
  }

  function addExp(v) {
    v = v || {}; var i = xi++;
    var d = document.createElement('div'); d.className = 'row-card';
    d.innerHTML = '<span class="tag">Pengalaman</span><button type="button" class="btn-rm">Hapus</button><div class="grid">' +
      '<div><label>Perusahaan</label><input name="experiences[' + i + '][company_name]" value="' + esc(v.company_name) + '"></div>' +
      '<div><label>Jabatan</label><input name="experiences[' + i + '][job_title]" value="' + esc(v.job_title) + '"></div>' +
      '<div><label>Mulai</label><input type="date" name="experiences[' + i + '][start_date]" value="' + esc(v.start_date) + '"></div>' +
      '<div><label>Selesai <small>(kosong = masih bekerja)</small></label><input type="date" name="experiences[' + i + '][end_date]" value="' + esc(v.end_date) + '"></div>' +
      '<div class="full"><label>Gaji Terakhir (Rp)</label><input name="experiences[' + i + '][last_salary]" inputmode="numeric" value="' + esc(v.last_salary) + '"></div>' +
      '<div class="full"><label>Deskripsi Pekerjaan</label><textarea rows="2" name="experiences[' + i + '][job_description]">' + esc(v.job_description) + '</textarea></div></div>';
    d.querySelector('.btn-rm').onclick = function () { d.remove(); };
    document.getElementById('expList').appendChild(d);
  }

  document.getElementById('addEdu').onclick = function () { addEdu(); };
  document.getElementById('addExp').onclick = function () { addExp(); };
  (Array.isArray(oldEdu) ? oldEdu : Object.values(oldEdu)).forEach(addEdu);
  (Array.isArray(oldExp) ? oldExp : Object.values(oldExp)).forEach(addExp);
  if (!document.querySelector('#eduList .row-card')) addEdu();

  // Mulai dari langkah yang punya error dari server (kalau ada), selain itu langkah 1.
  var start = 1;
  var firstErr = document.querySelector('.err');
  if (firstErr) { var p = firstErr.closest('.panel'); if (p) start = +p.dataset.p; }
  show(start);
  window.scrollTo(0, 0);

  // Modal tata cara — otomatis muncul saat link dibuka (kecuali kembali dari error validasi server).
  var guide = document.getElementById('guide');
  function openGuide() { guide.classList.add('on'); document.body.style.overflow = 'hidden'; document.getElementById('guideClose').focus(); }
  function closeGuide() { guide.classList.remove('on'); document.body.style.overflow = ''; }
  document.getElementById('guideOpen').onclick = openGuide;
  document.getElementById('guideClose').onclick = closeGuide;
  guide.addEventListener('click', function (e) { if (e.target === guide) closeGuide(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeGuide(); });
  if (!document.querySelector('.err')) openGuide();

})();
</script>
</body>
</html>
