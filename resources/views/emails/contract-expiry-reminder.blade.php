<x-mail::message>
# Pengingat Kontrak Karyawan

Halo Tim HRD,

Berikut adalah rekap status kontrak karyawan per **{{ now()->format('d F Y') }}** yang memerlukan perhatian Anda.

@if($expired->isNotEmpty())
## Kontrak Sudah Berakhir

Karyawan berikut kontraknya **sudah berakhir** namun masih berstatus aktif:

<x-mail::table>
| Nama Karyawan | NIK | Tanggal Berakhir | Divisi |
|:---|:---|:---|:---|
@foreach($expired as $e)
| {{ $e->name }} | {{ $e->nip ?? '-' }} | {{ $e->contract_end_date->format('d M Y') }} | {{ $e->department ?? '-' }} |
@endforeach
</x-mail::table>

@endif

@if($expiring->isNotEmpty())
## Kontrak Akan Berakhir (≤ 2 Bulan)

Karyawan berikut kontraknya akan berakhir dalam **60 hari ke depan**:

<x-mail::table>
| Nama Karyawan | NIK | Tanggal Berakhir | Sisa Hari | Divisi |
|:---|:---|:---|:---|:---|
@foreach($expiring as $e)
| {{ $e->name }} | {{ $e->nip ?? '-' }} | {{ $e->contract_end_date->format('d M Y') }} | {{ now()->startOfDay()->diffInDays($e->contract_end_date->startOfDay()) }} hari | {{ $e->department ?? '-' }} |
@endforeach
</x-mail::table>

@endif

Segera tindak lanjuti dengan perpanjangan atau penghentian kontrak sesuai kebijakan perusahaan.

<x-mail::button :url="url('/appraisal/employees')" color="primary">
Kelola Data Karyawan
</x-mail::button>

Salam,
**SIPRO — PT. Pro Energi**
</x-mail::message>
