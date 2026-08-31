<x-mail::message>
# Update Lamaran Anda

Halo {{ $candidate->name }},

Terima kasih atas waktu dan minat Anda melamar
@if($candidate->jobRequisition) posisi **{{ $candidate->jobRequisition->title }}** di @else di @endif
**PT Pro Energi Group**, serta atas kesediaan Anda mengikuti proses seleksi kami.

Setelah melalui proses evaluasi yang cermat, dengan berat hati kami sampaikan bahwa
untuk saat ini kami belum dapat melanjutkan proses lamaran Anda ke tahap berikutnya.
Keputusan ini murni berdasarkan kesesuaian kebutuhan posisi saat ini, bukan penilaian
atas kemampuan Anda secara keseluruhan.

Kami sangat menghargai waktu yang telah Anda luangkan, dan data Anda akan kami simpan
untuk kesempatan yang mungkin lebih sesuai di masa mendatang.

Kami mengucapkan terima kasih sekali lagi dan mendoakan yang terbaik untuk langkah
karier Anda selanjutnya.

Salam,
**Tim Rekrutmen — PT Pro Energi Group**
</x-mail::message>
