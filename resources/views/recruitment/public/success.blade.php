<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lamaran Terkirim — ProEnergi</title>
<link rel="icon" type="image/png" href="{{ asset('img/propeople-icon.png') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing:border-box; }
  body { margin:0; font-family:'Inter',system-ui,sans-serif; color:#1b2638; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px;
    background:radial-gradient(900px 400px at 90% -10%, rgba(242,169,0,.2), transparent 60%), linear-gradient(135deg,#0b2447,#19376d); }
  .box { background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,.35); padding:44px 32px 36px; max-width:480px; width:100%; text-align:center; animation:pop .5s cubic-bezier(.2,1.2,.3,1); }
  @keyframes pop { from { opacity:0; transform:translateY(20px) scale(.96); } to { opacity:1; transform:none; } }
  .logo { height:30px; margin-bottom:22px; }
  .ico { width:84px; height:84px; border-radius:50%; margin:0 auto 20px; display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,#f2a900,#ffc83d); box-shadow:0 10px 28px rgba(242,169,0,.45); }
  .ico svg { width:42px; height:42px; stroke:#0b2447; stroke-dasharray:30; stroke-dashoffset:30; animation:draw .6s .35s ease forwards; }
  @keyframes draw { to { stroke-dashoffset:0; } }
  h1 { font-size:1.45rem; font-weight:800; color:#0b2447; margin:0 0 10px; letter-spacing:-.01em; }
  p { color:#64748b; font-size:.93rem; line-height:1.65; margin:0; }
  .next { text-align:left; background:#f4f7fb; border-radius:14px; padding:16px 18px; margin:24px 0 0; }
  .next b { display:block; font-size:.78rem; text-transform:uppercase; letter-spacing:.08em; color:#19376d; margin-bottom:8px; }
  .next ol { margin:0; padding-left:18px; font-size:.86rem; color:#334155; line-height:1.8; }
</style>
</head>
<body>
  <div class="box">
    <img class="logo" src="{{ asset('img/logo-pe.png') }}" alt="ProEnergi" onerror="this.style.display='none'">
    <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
    <h1>Lamaran Anda Telah Terkirim</h1>
    <p>Terima kasih telah melamar di ProEnergi. Data Anda sudah kami terima dengan baik.</p>
    <div class="next">
      <b>Langkah selanjutnya</b>
      <ol>
        <li>Tim HRD meninjau lamaran Anda.</li>
        <li>Jika sesuai, kami menghubungi Anda lewat email atau WhatsApp.</li>
        <li>Anda diundang ke tahap seleksi berikutnya.</li>
      </ol>
    </div>
  </div>
</body>
</html>
