<?php

namespace App\Support;

/**
 * Angka ke teks Bahasa Indonesia — dipakai antara lain di slip gaji
 * ("... Rupiah") supaya nominal punya pembanding tertulis.
 */
class Terbilang
{
    private const SATUAN = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima',
        'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
    ];

    public static function make(int|float $number): string
    {
        $number = (int) round(abs($number));

        if ($number === 0) {
            return 'nol';
        }

        return trim(preg_replace('/\s+/', ' ', self::convert($number)));
    }

    /** Versi rapi untuk mata uang: "Seratus Ribu Rupiah". */
    public static function rupiah(int|float $number): string
    {
        return ucwords(self::make($number)) . ' Rupiah';
    }

    private static function convert(int $number): string
    {
        if ($number < 12) {
            return self::SATUAN[$number];
        }

        if ($number < 20) {
            return self::convert($number - 10) . ' belas';
        }

        if ($number < 100) {
            return self::convert(intdiv($number, 10)) . ' puluh ' . self::convert($number % 10);
        }

        if ($number < 200) {
            return 'seratus ' . self::convert($number - 100);
        }

        if ($number < 1000) {
            return self::convert(intdiv($number, 100)) . ' ratus ' . self::convert($number % 100);
        }

        if ($number < 2000) {
            return 'seribu ' . self::convert($number - 1000);
        }

        if ($number < 1_000_000) {
            return self::convert(intdiv($number, 1000)) . ' ribu ' . self::convert($number % 1000);
        }

        if ($number < 1_000_000_000) {
            return self::convert(intdiv($number, 1_000_000)) . ' juta ' . self::convert($number % 1_000_000);
        }

        if ($number < 1_000_000_000_000) {
            return self::convert(intdiv($number, 1_000_000_000)) . ' miliar ' . self::convert($number % 1_000_000_000);
        }

        return self::convert(intdiv($number, 1_000_000_000_000)) . ' triliun ' . self::convert($number % 1_000_000_000_000);
    }
}
