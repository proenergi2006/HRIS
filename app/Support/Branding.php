<?php

namespace App\Support;

use App\Models\Company;

/**
 * Identitas visual perusahaan untuk kop/cetakan PDF.
 *
 * Logo perusahaan disimpan sebagai file di public/img/logo-{code}.png
 * (mis. public/img/logo-proenergi.png). Perusahaan tanpa file logo cukup
 * ditampilkan dengan nama teks saja — begitu file logo-{code}.png ditambahkan,
 * semua cetakan perusahaan itu otomatis ikut menampilkannya.
 */
class Branding
{
    private static array $logoCache = [];
    private static array $companyCache = [];

    /**
     * Data URI (base64) logo perusahaan untuk disematkan di PDF, atau null
     * kalau perusahaan tsb belum punya file logo. Data URI dipakai supaya
     * aman apa pun setelan chroot/isRemoteEnabled DomPDF.
     */
    public static function pdfLogo(?string $companyCode): ?string
    {
        if (! $companyCode) {
            return null;
        }

        if (array_key_exists($companyCode, self::$logoCache)) {
            return self::$logoCache[$companyCode];
        }

        $uri = null;
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $path = public_path('img/logo-' . $companyCode . '.' . $ext);
            if (is_file($path)) {
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $uri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                break;
            }
        }

        return self::$logoCache[$companyCode] = $uri;
    }

    /**
     * Terima Company|string kode|null, kembalikan Company|null (dengan cache
     * kecil supaya aman dipanggil dari view berkali-kali).
     */
    public static function resolveCompany(Company|string|null $company): ?Company
    {
        if ($company instanceof Company) {
            return $company;
        }

        if (! is_string($company) || $company === '') {
            return null;
        }

        return self::$companyCache[$company]
            ??= Company::where('code', $company)->first();
    }
}
