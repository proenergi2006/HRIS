<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kebersihan - {{ $room->name }}</title>
  <link rel="stylesheet" href="{{ asset('graindashboard/css/graindashboard.css') }}">
  <style>
    body { background: #f4f6fa; min-height: 100vh; }
    .hero { background: linear-gradient(135deg, #1a3c5e 0%, #2e6da4 100%); color:#fff; padding: 2rem 1rem 1.5rem; text-align:center; }
    .hero h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: .25rem; }
    .hero p  { font-size: .9rem; opacity: .85; margin:0; }
    .form-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); margin: 1.5rem auto; max-width: 640px; overflow:hidden; }
    .item-block { border: 1px solid #e9ecef; border-radius:8px; margin-bottom:1rem; overflow:hidden; }
    .item-header { background:#f8f9fa; padding:.65rem 1rem; font-weight:600; font-size:.95rem; border-bottom:1px solid #e9ecef; }
    .item-body   { padding:1rem; }

    /* Photo section */
    .photo-thumbs { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:.5rem; min-height:0; }
    .thumb-wrap { position:relative; }
    .thumb-wrap img { width:72px; height:72px; object-fit:cover; border-radius:8px; border:1px solid #dee2e6; display:block; }
    .thumb-remove { position:absolute; top:-6px; right:-6px; width:18px; height:18px; border-radius:50%; background:#dc3545;
                    color:#fff; border:none; cursor:pointer; font-size:11px; line-height:18px; text-align:center; padding:0; }
    .btn-camera { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .9rem; border-radius:8px;
                  border:2px dashed #adb5bd; background:#f8f9fa; color:#495057; cursor:pointer; font-size:.85rem;
                  font-weight:600; transition:.15s; }
    .btn-camera:hover { border-color:#2e6da4; color:#2e6da4; background:#e8f0fb; }
    .hidden-inputs { display:none; }

    footer { text-align:center; font-size:.8rem; color:#adb5bd; padding:1rem 0 2rem; }
  </style>
</head>
<body>

<div class="hero">
  <div style="display:inline-block;background:#fff;border-radius:10px;padding:7px 18px;margin-bottom:12px;">
    <img src="/img/logo-proenergi.png" alt="PT. Pro Energi" style="height:36px;object-fit:contain;display:block;">
  </div>
  <h1>Laporan Kebersihan</h1>
  <p>{{ $room->name }}{{ $room->location ? ' — ' . $room->location : '' }}</p>
</div>

<div style="max-width:640px; margin:0 auto; padding:0 1rem;">

  @if(session('error'))
    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
  @endif

  @if($items->isEmpty())
    <div class="alert alert-warning mt-3">Belum ada item kebersihan untuk ruangan ini. Hubungi administrator.</div>
  @else

  <form method="POST" action="{{ route('ga.room.submit', $room) }}" enctype="multipart/form-data" id="cleanForm">
    @csrf

    <div class="form-card p-3 mt-3">
      <div class="form-group mb-0">
        <label class="font-weight-bold">Nama Petugas <span class="text-danger">*</span></label>
        <input type="text" name="cleaner_name" class="form-control @error('cleaner_name') is-invalid @enderror"
               value="{{ old('cleaner_name') }}" placeholder="Masukkan nama Anda" required>
        @error('cleaner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    @foreach($items as $item)
    <div class="item-block">
      <div class="item-header">{{ $loop->iteration }}. {{ $item->name }}</div>
      <div class="item-body">

        <label class="small font-weight-bold mb-1">Foto</label>

        {{-- Thumbnail preview area --}}
        <div class="photo-thumbs" id="thumbs-{{ $item->id }}"></div>

        {{-- Camera button --}}
        <button type="button" class="btn-camera" data-item="{{ $item->id }}">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
            <circle cx="12" cy="13" r="4"/>
          </svg>
          Ambil Foto
        </button>

        {{-- Hidden inputs accumulate here --}}
        <div class="hidden-inputs" id="inputs-{{ $item->id }}"></div>

        {{-- Notes --}}
        <div class="mt-3">
          <label class="small font-weight-bold mb-1">Keterangan</label>
          <textarea name="items[{{ $item->id }}][notes]" class="form-control form-control-sm"
                    rows="2" placeholder="Keterangan tambahan (opsional)">{{ old("items.{$item->id}.notes") }}</textarea>
        </div>

      </div>
    </div>
    @endforeach

    <div class="mt-3 mb-4">
      <button type="submit" class="btn btn-primary btn-block btn-lg">
        Kirim Laporan Kebersihan
      </button>
    </div>
  </form>

  @endif
</div>

<footer>SIPRO &mdash; PT. Pro Energi</footer>

<script>
// Resize + compress ke JPEG (maks sisi terpanjang 1280px, quality 0.8) sebelum
// dipakai sebagai thumbnail atau diupload — foto kamera mentah bisa 3-8MB dan
// kalau banyak foto sekaligus bikin browser lag serta gampang kena limit
// upload di server. Fallback ke file asli kalau kompresi gagal/tidak didukung.
function compressImage(file, callback) {
  if (!window.createImageBitmap && !window.Image) { callback(file); return; }

  var img = new Image();
  var url = URL.createObjectURL(file);

  img.onload = function() {
    URL.revokeObjectURL(url);

    var MAX = 1280;
    var w = img.naturalWidth, h = img.naturalHeight;
    if (w > MAX || h > MAX) {
      if (w >= h) { h = Math.round(h * (MAX / w)); w = MAX; }
      else        { w = Math.round(w * (MAX / h)); h = MAX; }
    }

    var canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    canvas.getContext('2d').drawImage(img, 0, 0, w, h);

    if (canvas.toBlob) {
      canvas.toBlob(function(blob) { callback(blob || file); }, 'image/jpeg', 0.8);
    } else {
      callback(file);
    }
  };
  img.onerror = function() {
    URL.revokeObjectURL(url);
    callback(file);
  };
  img.src = url;
}

document.querySelectorAll('.btn-camera').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var itemId = this.dataset.item;

    var input = document.createElement('input');
    input.type = 'file';
    input.name = 'items[' + itemId + '][photos][]';
    input.accept = 'image/*';
    input.setAttribute('capture', 'environment');
    input.style.display = 'none';

    input.addEventListener('change', function() {
      var file = this.files[0];
      if (!file) return;
      var self = this;

      compressImage(file, function(blob) {
        // Ganti isi input dengan versi yang sudah dikompres, supaya yang
        // terupload adalah file kecil, bukan foto kamera mentah.
        try {
          var dt = new DataTransfer();
          dt.items.add(new File([blob], 'foto.jpg', { type: 'image/jpeg' }));
          self.files = dt.files;
        } catch (e) {
          // DataTransfer tidak didukung — biarkan file asli yang terkirim
        }

        // move input into the form's hidden container so it submits
        document.getElementById('inputs-' + itemId).appendChild(self);

        // show thumbnail with remove button (pakai object URL, bukan base64 penuh)
        var thumbsEl = document.getElementById('thumbs-' + itemId);
        var wrap = document.createElement('div');
        wrap.className = 'thumb-wrap';

        var img = document.createElement('img');
        img.src = URL.createObjectURL(blob);

        var rm = document.createElement('button');
        rm.type = 'button';
        rm.className = 'thumb-remove';
        rm.innerHTML = '&times;';
        rm.addEventListener('click', function() {
          self.remove(); // remove input from DOM → won't submit
          wrap.remove();
        });

        wrap.appendChild(img);
        wrap.appendChild(rm);
        thumbsEl.appendChild(wrap);
      });
    });

    document.body.appendChild(input);
    input.click();
  });
});
</script>
</body>
</html>
