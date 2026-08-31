{{--
  Kop surat PDF — dipakai di seluruh cetakan.

  Param:
    $company   App\Models\Company | string(kode) | null
               null  = cetakan konsolidasi lintas-PT (tanpa logo, pakai $groupLabel)
    $groupLabel (opsional) label saat $company null. Default "Konsolidasi Grup".
    $docTitle   (opsional) judul dokumen tepat di bawah kop.
    $meta       (opsional) baris kecil di bawah judul (mis. periode / no. dokumen).

  Logo tampil otomatis kalau ada file public/img/logo-{code}.png.
--}}
@php
    $kopCompany = \App\Support\Branding::resolveCompany($company ?? null);
    $kopLogo    = $kopCompany ? \App\Support\Branding::pdfLogo($kopCompany->code) : null;

    if ($kopCompany) {
        $kopName = $kopCompany->name;
        $kopSub  = collect([
            $kopCompany->address,
            $kopCompany->phone ? 'Telp ' . $kopCompany->phone : null,
            $kopCompany->email,
        ])->filter()->implode('  ·  ');

        // Kolom companies masih kosong utk perusahaan utama? pakai default config.
        if ($kopSub === '' && $kopCompany->code === config('sipro.company.code')) {
            $kopSub = collect([
                config('sipro.company.tagline'),
                config('sipro.company.address'),
                config('sipro.company.website'),
            ])->filter()->implode('  ·  ');
        }
    } else {
        $kopName = $groupLabel ?? 'Konsolidasi Grup (Semua PT)';
        $kopSub  = config('sipro.company.website');
    }
@endphp
<table style="width:100%;border-collapse:collapse;margin:0 0 4px;">
  <tr>
    @if($kopLogo)
      <td style="width:112px;vertical-align:middle;padding:0 12px 6px 0;">
        <img src="{{ $kopLogo }}" alt="" style="width:104px;">
      </td>
    @endif
    <td style="vertical-align:middle;padding-bottom:6px;">
      <div style="font-size:13.5pt;font-weight:bold;color:#0F2A4A;letter-spacing:.3px;">{{ strtoupper($kopName) }}</div>
      @if(!empty($kopSub))
        <div style="font-size:7pt;color:#777;margin-top:2px;">{{ $kopSub }}</div>
      @endif
    </td>
  </tr>
</table>
<div style="height:2px;background:#0F2A4A;margin-bottom:2px;"></div>
<div style="height:1px;background:#c9a13b;margin-bottom:12px;"></div>

@if(!empty($docTitle))
  <div style="text-align:center;margin-bottom:12px;">
    <div style="font-size:12pt;font-weight:bold;text-transform:uppercase;letter-spacing:1px;color:#0F2A4A;">{{ $docTitle }}</div>
    @if(!empty($meta))
      <div style="font-size:8pt;color:#666;margin-top:3px;">{{ $meta }}</div>
    @endif
  </div>
@endif
