<?php

/**
 * Starter dataset Kecamatan & Kelurahan untuk kota-kota besar (lokasi kantor
 * paling mungkin). Bukan dataset lengkap Indonesia — dataset penuh (~7rb
 * kecamatan, ~83rb kelurahan) di-import terpisah dari wilayah.id / Kemendagri
 * (lihat PRD Bab 12). Struktur file ini sengaja dibuat mudah diperluas:
 *
 *   'districts' => [ '<city_code>' => ['Nama Kecamatan', ...], ... ]
 *   'villages'  => [ '<city_code>::<Nama Kecamatan>' => ['Nama Kelurahan', ...], ... ]
 *
 * Kode district/village di-generate otomatis oleh seeder (prefix kode parent).
 */

return [
    'districts' => [
        // DKI Jakarta
        '3171' => [ // Jakarta Selatan
            'Kebayoran Baru', 'Kebayoran Lama', 'Pesanggrahan', 'Cilandak', 'Pasar Minggu',
            'Jagakarsa', 'Mampang Prapatan', 'Pancoran', 'Tebet', 'Setiabudi',
        ],
        '3172' => [ // Jakarta Timur
            'Matraman', 'Pulogadung', 'Jatinegara', 'Duren Sawit', 'Kramat Jati',
            'Makasar', 'Pasar Rebo', 'Ciracas', 'Cipayung', 'Cakung',
        ],
        '3173' => [ // Jakarta Pusat
            'Gambir', 'Tanah Abang', 'Menteng', 'Senen', 'Cempaka Putih',
            'Johar Baru', 'Kemayoran', 'Sawah Besar',
        ],
        '3174' => [ // Jakarta Barat
            'Kembangan', 'Kebon Jeruk', 'Palmerah', 'Grogol Petamburan', 'Tambora',
            'Taman Sari', 'Cengkareng', 'Kalideres',
        ],
        '3175' => [ // Jakarta Utara
            'Penjaringan', 'Pademangan', 'Tanjung Priok', 'Koja', 'Kelapa Gading', 'Cilincing',
        ],

        // Kota besar lain
        '3273' => [ // Bandung (kota)
            'Sukasari', 'Coblong', 'Cidadap', 'Bandung Wetan', 'Sumur Bandung',
            'Andir', 'Cicendo', 'Lengkong', 'Regol', 'Batununggal', 'Buahbatu', 'Antapani',
        ],
        '3578' => [ // Surabaya
            'Genteng', 'Tegalsari', 'Gubeng', 'Wonokromo', 'Sawahan', 'Tandes',
            'Rungkut', 'Sukolilo', 'Mulyorejo', 'Tenggilis Mejoyo', 'Wiyung', 'Lakarsantri',
        ],
        '1271' => [ // Medan
            'Medan Kota', 'Medan Baru', 'Medan Polonia', 'Medan Petisah', 'Medan Timur',
            'Medan Barat', 'Medan Maimun', 'Medan Selayang', 'Medan Sunggal', 'Medan Helvetia',
        ],
        '3674' => [ // Tangerang Selatan
            'Serpong', 'Serpong Utara', 'Pondok Aren', 'Ciputat', 'Ciputat Timur',
            'Pamulang', 'Pondok Betung', 'Setu',
        ],
        '3275' => [ // Bekasi (kota)
            'Bekasi Timur', 'Bekasi Barat', 'Bekasi Utara', 'Bekasi Selatan', 'Rawalumbu',
            'Medan Satria', 'Pondok Gede', 'Jatiasih', 'Jatisampurna', 'Mustika Jaya', 'Bantargebang', 'Pondok Melati',
        ],
        '3276' => [ // Depok
            'Beji', 'Pancoran Mas', 'Cipayung', 'Sukmajaya', 'Cilodong', 'Limo',
            'Cinere', 'Cimanggis', 'Tapos', 'Sawangan', 'Bojongsari',
        ],
    ],

    'villages' => [
        // Jakarta Selatan — Kebayoran Baru
        '3171::Kebayoran Baru' => [
            'Selong', 'Gunung', 'Kramat Pela', 'Gandaria Utara', 'Cipete Utara',
            'Pulo', 'Melawai', 'Petogogan', 'Rawa Barat', 'Senayan',
        ],
        // Jakarta Selatan — Setiabudi
        '3171::Setiabudi' => [
            'Setiabudi', 'Karet', 'Karet Semanggi', 'Karet Kuningan', 'Kuningan Timur',
            'Menteng Atas', 'Pasar Manggis', 'Guntur',
        ],
        // Jakarta Selatan — Tebet
        '3171::Tebet' => [
            'Tebet Timur', 'Tebet Barat', 'Menteng Dalam', 'Kebon Baru', 'Bukit Duri', 'Manggarai', 'Manggarai Selatan',
        ],
        // Jakarta Pusat — Menteng
        '3173::Menteng' => [
            'Menteng', 'Pegangsaan', 'Cikini', 'Gondangdia', 'Kebon Sirih',
        ],
        // Jakarta Pusat — Gambir
        '3173::Gambir' => [
            'Gambir', 'Cideng', 'Petojo Utara', 'Petojo Selatan', 'Kebon Kelapa', 'Duri Pulo',
        ],
        // Jakarta Pusat — Tanah Abang
        '3173::Tanah Abang' => [
            'Bendungan Hilir', 'Karet Tengsin', 'Kebon Melati', 'Kebon Kacang', 'Kampung Bali', 'Petamburan', 'Gelora',
        ],
    ],
];
