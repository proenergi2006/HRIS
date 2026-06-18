<x-mail::message>
# Laporan Pengaduan Baru Masuk

Halo Tim HRD,

Terdapat laporan pengaduan baru yang diterima melalui **Whistleblower System** PT. Pro Energi.

<x-mail::panel>
**No. Tiket:** {{ $report->ticket_number }}
**Kategori:** {{ $report->category }}
**Tanggal:** {{ $report->created_at->format('d F Y, H:i') }} WIB
**Pelapor:** {{ $report->is_anonymous ? 'Anonim' : ($report->reporter_name ?? 'Tidak disebutkan') }}
**Lampiran:** {{ $report->attachment_path ? 'Ada' : 'Tidak ada' }}
</x-mail::panel>

**Uraian Singkat:**

{{ \Illuminate\Support\Str::limit($report->description, 300) }}

Silakan login ke SIPRO untuk melihat detail lengkap dan menindaklanjuti laporan ini.

<x-mail::button :url="route('whistleblower.admin.show', $report)" color="primary">
Lihat Detail Laporan
</x-mail::button>

Laporan ini bersifat **rahasia** dan hanya boleh diakses oleh personel yang berwenang.

Salam,
**SIPRO — PT. Pro Energi**
</x-mail::message>
