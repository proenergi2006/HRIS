<x-mail::message>
# Pengingat Dokumen Karyawan

Halo Tim HRD,

Berikut adalah rekap dokumen karyawan (KTP, SIM, paspor, sertifikasi, KITAS, dll) per **{{ now()->format('d F Y') }}** yang memerlukan perhatian Anda.

@if($expired->isNotEmpty())
## Sudah Kadaluarsa

<x-mail::table>
| Nama Karyawan | NIK | Jenis Dokumen | Tanggal Kadaluarsa |
|:---|:---|:---|:---|
@foreach($expired as $d)
| {{ $d->employee->name ?? '-' }} | {{ $d->employee->nip ?? '-' }} | {{ $d->doc_type }} | {{ $d->expires_at->format('d M Y') }} |
@endforeach
</x-mail::table>

@endif

@if($expiring->isNotEmpty())
## Akan Kadaluarsa (≤ 60 Hari)

<x-mail::table>
| Nama Karyawan | NIK | Jenis Dokumen | Tanggal Kadaluarsa | Sisa Hari |
|:---|:---|:---|:---|:---|
@foreach($expiring as $d)
| {{ $d->employee->name ?? '-' }} | {{ $d->employee->nip ?? '-' }} | {{ $d->doc_type }} | {{ $d->expires_at->format('d M Y') }} | {{ now()->startOfDay()->diffInDays($d->expires_at->startOfDay()) }} hari |
@endforeach
</x-mail::table>

@endif

Segera hubungi karyawan terkait untuk memperbarui dokumen.

<x-mail::button :url="url('/appraisal/employees')" color="primary">
Kelola Data Karyawan
</x-mail::button>

Salam,
**ProPeople — PT. Pro Energi**
</x-mail::message>
