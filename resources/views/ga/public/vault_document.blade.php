<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dokumen Brankas - {{ $document->barcode }}</title>
  <link rel="stylesheet" href="{{ asset('graindashboard/css/graindashboard.css') }}">
  <style>
    body { background: #f4f6fa; min-height: 100vh; }
    .hero { background: linear-gradient(135deg, #1a3c5e 0%, #2e6da4 100%); color:#fff; padding: 2rem 1rem 1.5rem; text-align:center; }
    .hero h1 { font-size: 1.3rem; font-weight: 700; margin-bottom: .25rem; }
    .hero .code { display:inline-block;background:#fff;color:#1a3c5e;border-radius:8px;padding:4px 16px;font-size:1rem;font-weight:800;letter-spacing:.1em;margin-top:6px }
    .hero .cat  { font-size:.85rem; opacity:.85; margin-top:8px }
    .back-link { display:block; text-align:center; margin-top:8px; }
    .back-link a { color:#fff; opacity:.85; font-size:.8rem; }
    .status-bar{display:flex;justify-content:center;margin-top:-18px}
    .status-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 20px;border-radius:999px;font-weight:700;font-size:13px;box-shadow:0 4px 16px rgba(0,0,0,.15)}
    .status-pill.available{background:#22c55e;color:#fff}
    .status-pill.taken{background:#f59e0b;color:#fff}
    .form-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); margin: 1.5rem auto 2rem; max-width: 560px; overflow:hidden; padding:1.25rem; }
    .detail-box { background:#f8f9fa; border-radius:8px; padding:.75rem 1rem; font-size:.85rem; color:#495057; margin-bottom:1rem; }
    .camera-box{position:relative;border-radius:10px;overflow:hidden;background:#111;aspect-ratio:4/3}
    .camera-box video, .camera-box img{width:100%;height:100%;object-fit:cover;display:block}
    .camera-box .cam-msg{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:1rem;text-align:center;color:#fff;font-size:.85rem;background:#111}
    footer { text-align:center; font-size:.8rem; color:#adb5bd; padding:1rem 0 2rem; }
  </style>
</head>
<body>

<div class="hero">
  <div style="display:inline-block;background:#fff;border-radius:10px;padding:7px 18px;margin-bottom:12px;">
    <img src="/img/logo-proenergi.png" alt="PT. Pro Energi" style="height:36px;object-fit:contain;display:block;">
  </div>
  <h1>Dokumen Brankas</h1>
  <div class="code">{{ $document->barcode }}</div>
  <div class="cat">{{ $vault->name }}</div>
  <div class="back-link"><a href="{{ route('ga.vault.scan', $vault) }}">&larr; Kembali ke daftar dokumen {{ $vault->name }}</a></div>
</div>

<div class="status-bar">
  @if($document->isOut())
    <div class="status-pill taken">Sedang Diambil</div>
  @else
    <div class="status-pill available">Di Brankas</div>
  @endif
</div>

<div class="form-card">

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="detail-box">{{ $document->detail }}</div>

  <form method="POST" action="{{ route('ga.vault.submit', [$vault, $document]) }}" enctype="multipart/form-data" id="vaultForm">
    @csrf
    @if($errors->any())
      <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="form-group">
      <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
      <div>
        <div class="custom-control custom-radio custom-control-inline">
          <input type="radio" id="status-pengambilan" name="status" value="pengambilan" class="custom-control-input" required
                 {{ old('status') == 'pengambilan' ? 'checked' : '' }}>
          <label class="custom-control-label" for="status-pengambilan">Pengambilan</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
          <input type="radio" id="status-pengembalian" name="status" value="pengembalian" class="custom-control-input"
                 {{ old('status') == 'pengembalian' ? 'checked' : '' }}>
          <label class="custom-control-label" for="status-pengembalian">Pengembalian</label>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="font-weight-bold">Tanggal <span class="text-danger">*</span></label>
      <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
    </div>

    <div class="form-group">
      <label class="font-weight-bold">Nama <span class="text-danger">*</span></label>
      <input type="text" name="nama" class="form-control" placeholder="Nama yang mengambil/mengembalikan"
             value="{{ old('nama') }}" required>
    </div>

    <div class="form-group">
      <label class="font-weight-bold">Keperluan <span class="text-danger">*</span></label>
      <select name="keperluan" class="form-control" required>
        <option value="">-- Pilih Keperluan --</option>
        <option value="pinjam" {{ old('keperluan') == 'pinjam' ? 'selected' : '' }}>Pinjam</option>
        <option value="jual" {{ old('keperluan') == 'jual' ? 'selected' : '' }}>Jual</option>
        <option value="pengembalian_jaminan" {{ old('keperluan') == 'pengembalian_jaminan' ? 'selected' : '' }}>Pengembalian Jaminan</option>
        <option value="pengambilan_dokumen" {{ old('keperluan') == 'pengambilan_dokumen' ? 'selected' : '' }}>Pengambilan Dokumen</option>
      </select>
    </div>

    <div class="form-group mb-0">
      <label class="font-weight-bold">Foto Serah Terima <span class="text-danger">*</span></label>
      <div class="camera-box" id="camera-box">
        <video id="camera-video" autoplay playsinline muted></video>
        <img id="camera-result" style="display:none">
        <div id="camera-msg" class="cam-msg" style="display:none"></div>
      </div>
      <canvas id="camera-canvas" class="d-none"></canvas>
      <div class="text-center mt-2">
        <button type="button" class="btn btn-primary btn-sm" id="btn-shutter">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-2px">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
          </svg>
          Jepret Foto
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-retake" style="display:none">
          Ambil Ulang
        </button>
      </div>
      <input type="file" name="photo_handover" id="photo_handover" class="d-none" accept="image/*" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" id="btn-submit">
      <span id="btn-text">Simpan Transaksi</span>
    </button>
  </form>
</div>

<footer>SIPRO &mdash; PT. Pro Energi</footer>

<script>
(function() {
  var video      = document.getElementById('camera-video');
  var canvas     = document.getElementById('camera-canvas');
  var resultImg  = document.getElementById('camera-result');
  var msgBox     = document.getElementById('camera-msg');
  var btnShutter = document.getElementById('btn-shutter');
  var btnRetake  = document.getElementById('btn-retake');
  var fileInput  = document.getElementById('photo_handover');
  var stream     = null;

  function showMsg(text) {
    msgBox.textContent = text;
    msgBox.style.display = 'flex';
    video.style.display = 'none';
    resultImg.style.display = 'none';
    btnShutter.style.display = 'none';
    btnRetake.style.display = 'none';
  }

  function stopCamera() {
    if (stream) {
      stream.getTracks().forEach(function(t) { t.stop(); });
      stream = null;
    }
  }

  function startCamera() {
    fileInput.value = '';
    resultImg.style.display = 'none';
    msgBox.style.display = 'none';
    video.style.display = 'block';
    btnShutter.style.display = 'inline-block';
    btnRetake.style.display = 'none';

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      showMsg('Browser ini tidak mendukung akses kamera langsung. Gunakan browser modern (Chrome/Safari terbaru).');
      return;
    }

    navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false })
      .then(function(s) {
        stream = s;
        video.srcObject = s;
      })
      .catch(function() {
        showMsg('Tidak bisa mengakses kamera. Izinkan akses kamera pada browser untuk melanjutkan.');
      });
  }

  btnShutter.addEventListener('click', function() {
    var w = video.videoWidth, h = video.videoHeight;
    if (!w || !h) return;
    canvas.width = w;
    canvas.height = h;
    canvas.getContext('2d').drawImage(video, 0, 0, w, h);

    canvas.toBlob(function(blob) {
      if (!blob) return;
      var file = new File([blob], 'serah-terima-' + Date.now() + '.jpg', { type: 'image/jpeg' });
      var dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;

      resultImg.src = canvas.toDataURL('image/jpeg', 0.9);
      resultImg.style.display = 'block';
      video.style.display = 'none';
      btnShutter.style.display = 'none';
      btnRetake.style.display = 'inline-block';

      stopCamera();
    }, 'image/jpeg', 0.9);
  });

  btnRetake.addEventListener('click', startCamera);

  startCamera();

  document.getElementById('vaultForm').addEventListener('submit', function(e) {
    if (!fileInput.files || fileInput.files.length === 0) {
      e.preventDefault();
      showMsg('Silakan jepret foto serah terima terlebih dahulu.');
      return;
    }
    document.getElementById('btn-submit').disabled = true;
    document.getElementById('btn-text').textContent = 'Menyimpan...';
  });
})();
</script>
</body>
</html>
