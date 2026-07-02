<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $vehicle->name }} — Penggunaan Kendaraan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f0f4f8;min-height:100vh;display:flex;flex-direction:column}
.hero{background:linear-gradient(135deg,#0f2a4a 0%,#1a3f6f 60%,#2563eb 100%);padding:28px 20px 64px;text-align:center;color:#fff}
.hero-badge{display:inline-block;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:4px 14px;font-size:12px;font-weight:600;letter-spacing:.05em;margin-bottom:14px}
.hero h1{font-size:clamp(1.5rem,5vw,2rem);font-weight:800;margin-bottom:4px}
.hero .plate{display:inline-block;background:#fff;color:#0f2a4a;border-radius:8px;padding:6px 20px;font-size:1.1rem;font-weight:800;letter-spacing:.12em;margin-top:8px}
.hero .meta{display:flex;gap:16px;justify-content:center;margin-top:14px;flex-wrap:wrap}
.hero .meta span{font-size:13px;opacity:.85}
.status-bar{display:flex;justify-content:center;margin-top:-24px;margin-bottom:0;padding:0 20px}
.status-pill{display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:999px;font-weight:700;font-size:14px;box-shadow:0 4px 20px rgba(0,0,0,.15)}
.status-pill.available{background:#22c55e;color:#fff}
.status-pill.in-use{background:#f59e0b;color:#fff}
.status-pill .dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.7);animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}
.container{max-width:520px;margin:0 auto;padding:24px 16px 48px;width:100%}
.card{background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.08);overflow:hidden;margin-top:24px}
.card-header{padding:18px 20px;border-bottom:1px solid #f1f5f9;font-weight:700;font-size:15px;color:#0f2a4a;display:flex;align-items:center;gap:8px}
.card-body{padding:20px}
.checkin-info{background:#fef3c7;border-radius:10px;padding:14px;margin-bottom:20px;font-size:13px;color:#92400e}
.checkin-info strong{display:block;font-size:14px;color:#78350f;margin-bottom:4px}
label{display:block;font-weight:600;font-size:13px;color:#374151;margin-bottom:5px}
.form-control{width:100%;padding:11px 14px;border:2px solid #e5e7eb;border-radius:10px;font-size:14px;font-family:inherit;transition:border-color .2s,box-shadow .2s;outline:none}
.form-control:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.form-group{margin-bottom:16px}
.photo-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.photo-grid.single{grid-template-columns:1fr}
.photo-box{position:relative;border:2px dashed #d1d5db;border-radius:12px;overflow:hidden;cursor:pointer;transition:border-color .2s,background .2s;background:#f9fafb}
.photo-box:hover,.photo-box.has-file{border-color:#2563eb;background:#eff6ff}
.photo-box input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.photo-box .inner{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px 10px;pointer-events:none}
.photo-box .emoji{font-size:28px;margin-bottom:6px}
.photo-box .label{font-size:12px;font-weight:600;color:#374151;text-align:center;line-height:1.3}
.photo-box .sub{font-size:11px;color:#9ca3af;margin-top:2px;text-align:center}
.photo-box.has-file .label{color:#2563eb}
.photo-upload-icon{font-size:22px;margin-bottom:6px;color:#9ca3af}
.photo-preview{position:absolute;inset:0;object-fit:cover;width:100%;height:100%;opacity:0;transition:opacity .3s}
.photo-preview.visible{opacity:1}
.btn{width:100%;padding:14px;border:none;border-radius:12px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-checkin{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff}
.btn-checkin:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(34,197,94,.4)}
.btn-checkout{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff}
.btn-checkout:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(245,158,11,.4)}
.btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
.km-wrap{position:relative}
.km-wrap .unit{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-weight:600;color:#6b7280;font-size:13px;pointer-events:none}
.alert{padding:14px 16px;border-radius:12px;font-size:14px;margin-bottom:20px;font-weight:500}
.alert-success{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0}
.alert-warning{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
.footer{text-align:center;font-size:12px;color:#9ca3af;padding-bottom:24px;margin-top:auto}
.spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<div class="hero">
  <div class="hero-badge">Penggunaan Kendaraan · PT Pro Energi</div>
  <h1>{{ $vehicle->name }}</h1>
  <div class="plate">{{ strtoupper($vehicle->plate) }}</div>
  <div class="meta">
    @if($vehicle->type) <span>{{ $vehicle->type }}</span> @endif
    @if($vehicle->color) <span>{{ $vehicle->color }}</span> @endif
    @if($vehicle->year) <span>{{ $vehicle->year }}</span> @endif
  </div>
</div>

<div class="status-bar">
  @if($activeUsage)
    <div class="status-pill in-use"><div class="dot"></div> Sedang Digunakan</div>
  @else
    <div class="status-pill available"><div class="dot"></div> Tersedia</div>
  @endif
</div>

<div class="container">

  @if(session('checkin_success'))
    <div class="alert alert-success">✅ {{ session('checkin_success') }}</div>
  @endif
  @if(session('checkout_success'))
    <div class="alert alert-success">✅ {{ session('checkout_success') }}</div>
  @endif

  @if($activeUsage)
  {{-- ══ CHECKOUT FORM ══ --}}
  <div class="card">
    <div class="card-header">
      Check Out Kendaraan
    </div>
    <div class="card-body">
      <div class="checkin-info">
        <strong>Sedang digunakan oleh: {{ $activeUsage->driver_name }}</strong>
        Check In: {{ $activeUsage->check_in_at->format('d M Y, H:i') }} &bull; Tujuan: {{ $activeUsage->destination }}
      </div>

      <form method="POST" action="{{ route('ga.checkout', $vehicle) }}" enctype="multipart/form-data" id="form-checkout">
        @csrf
        @if($errors->any())
          <div class="alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;margin-bottom:16px">
            {{ $errors->first() }}
          </div>
        @endif

        <div class="form-group">
          <label>KM Odometer Sekarang *</label>
          <div class="km-wrap">
            <input type="number" name="km_out" class="form-control" style="padding-right:50px"
                   placeholder="Contoh: 12500" min="0" required value="{{ old('km_out') }}">
            <span class="unit">KM</span>
          </div>
        </div>

        <div class="form-group">
          <label style="margin-bottom:10px">Foto Dokumentasi * <span style="font-weight:400;color:#6b7280">(klik kotak untuk pilih foto)</span></label>
          <div class="photo-box single" style="margin-bottom:12px" id="box-dashboard">
            <input type="file" name="photo_dashboard" accept="image/*" capture="environment"
                   onchange="previewPhoto(this,'prev-dashboard','box-dashboard')">
            <div class="inner">
              <img id="prev-dashboard" class="photo-preview">
              <div class="photo-upload-icon">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
              </div>
              <div class="label">Foto Dashboard + Odometer</div>
              <div class="sub">Wajib menampilkan angka KM</div>
            </div>
          </div>
          <div class="photo-grid">
            <div class="photo-box" id="box-front">
              <input type="file" name="photo_front" accept="image/*" capture="environment"
                     onchange="previewPhoto(this,'prev-front','box-front')">
              <div class="inner">
                <img id="prev-front" class="photo-preview">
                <div class="photo-upload-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></div>
                <div class="label">Sisi Depan</div>
                <div class="sub">Foto dari depan kendaraan</div>
              </div>
            </div>
            <div class="photo-box" id="box-back">
              <input type="file" name="photo_back" accept="image/*" capture="environment"
                     onchange="previewPhoto(this,'prev-back','box-back')">
              <div class="inner">
                <img id="prev-back" class="photo-preview">
                <div class="photo-upload-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></div>
                <div class="label">Sisi Belakang</div>
                <div class="sub">Foto dari belakang kendaraan</div>
              </div>
            </div>
            <div class="photo-box" id="box-left">
              <input type="file" name="photo_left" accept="image/*" capture="environment"
                     onchange="previewPhoto(this,'prev-left','box-left')">
              <div class="inner">
                <img id="prev-left" class="photo-preview">
                <div class="photo-upload-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></div>
                <div class="label">Sisi Kiri</div>
                <div class="sub">Foto dari sisi kiri</div>
              </div>
            </div>
            <div class="photo-box" id="box-right">
              <input type="file" name="photo_right" accept="image/*" capture="environment"
                     onchange="previewPhoto(this,'prev-right','box-right')">
              <div class="inner">
                <img id="prev-right" class="photo-preview">
                <div class="photo-upload-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></div>
                <div class="label">Sisi Kanan</div>
                <div class="sub">Foto dari sisi kanan</div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Keluhan / Catatan</label>
          <textarea name="keluhan" class="form-control" rows="3"
                    placeholder="Isi jika ada kerusakan, masalah, atau catatan penting..." style="resize:vertical">{{ old('keluhan') }}</textarea>
        </div>

        <button type="submit" class="btn btn-checkout" id="btn-checkout">
          <div class="spinner" id="sp-checkout"></div>
          <span id="txt-checkout">📤 Check Out Sekarang</span>
        </button>
      </form>
    </div>
  </div>

  @else
  {{-- ══ CHECKIN FORM ══ --}}
  <div class="card">
    <div class="card-header">
      Check In Kendaraan
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('ga.checkin', $vehicle) }}" id="form-checkin">
        @csrf
        @if($errors->any())
          <div class="alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca">
            {{ $errors->first() }}
          </div>
        @endif

        <div class="form-group">
          <label>Nama Peminjam *</label>
          <input type="text" name="driver_name" class="form-control"
                 placeholder="Nama lengkap" required value="{{ old('driver_name') }}">
        </div>
        <div class="form-group">
          <label>Tujuan *</label>
          <input type="text" name="destination" class="form-control"
                 placeholder="Contoh: Kantor Klien, Jl. Sudirman No.1" required value="{{ old('destination') }}">
        </div>

        <button type="submit" class="btn btn-checkin" id="btn-checkin">
          <div class="spinner" id="sp-checkin"></div>
          <span id="txt-checkin">📥 Check In Sekarang</span>
        </button>
      </form>
    </div>
  </div>
  @endif

</div>
<div class="footer">SIPRO · PT Pro Energi &copy; {{ date('Y') }}</div>

<script>
function previewPhoto(input, previewId, boxId) {
  var file = input.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    var prev = document.getElementById(previewId);
    var box  = document.getElementById(boxId);
    prev.src = e.target.result;
    prev.classList.add('visible');
    box.classList.add('has-file');
  };
  reader.readAsDataURL(file);
}
document.getElementById('form-checkin')?.addEventListener('submit', function() {
  document.getElementById('btn-checkin').disabled = true;
  document.getElementById('sp-checkin').style.display = 'block';
  document.getElementById('txt-checkin').textContent = 'Memproses...';
});
document.getElementById('form-checkout')?.addEventListener('submit', function() {
  document.getElementById('btn-checkout').disabled = true;
  document.getElementById('sp-checkout').style.display = 'block';
  document.getElementById('txt-checkout').textContent = 'Mengupload foto...';
});
</script>
</body>
</html>
