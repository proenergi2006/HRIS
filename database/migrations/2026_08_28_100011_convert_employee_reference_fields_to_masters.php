<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konversi field referensi karyawan dari ENUM/string bebas ke relasi
     * ke tabel master. Pola: tambah kolom FK -> backfill dari nilai lama.
     *
     * Kolom teks lama TIDAK di-drop (safety, cleanup terpisah setelah
     * verifikasi produksi), tapi `religion` di-rename jadi `religion_name`
     * supaya tidak bentrok dengan relasi Eloquent `religion()`.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('religion_id')->nullable()->after('religion')
                ->constrained('religions')->nullOnDelete();
            $table->foreignId('marital_status_id')->nullable()->after('marital_status')
                ->constrained('marital_statuses')->nullOnDelete();
            $table->foreignId('blood_type_id')->nullable()->after('blood_type')
                ->constrained('blood_types')->nullOnDelete();
            $table->foreignId('employee_type_id')->nullable()->after('employee_type')
                ->constrained('employee_types')->nullOnDelete();

            $table->foreignId('domicile_province_id')->nullable()->after('domicile_city')
                ->constrained('provinces')->nullOnDelete();
            $table->foreignId('domicile_city_id')->nullable()->after('domicile_province_id')
                ->constrained('cities')->nullOnDelete();
            $table->foreignId('ktp_province_id')->nullable()->after('ktp_city')
                ->constrained('provinces')->nullOnDelete();
            $table->foreignId('ktp_city_id')->nullable()->after('ktp_province_id')
                ->constrained('cities')->nullOnDelete();
        });

        // Backfill dulu (masih baca kolom `religion` yang lama), baru rename.
        $this->backfillByName('religion_id', 'religions', 'religion');
        $this->backfillByLegacyKey('marital_status_id', 'marital_statuses', 'marital_status');
        $this->backfillByName('blood_type_id', 'blood_types', 'blood_type');
        $this->backfillEmployeeType();
        $this->backfillCity('domicile_city', 'domicile_city_id', 'domicile_province_id');
        $this->backfillCity('ktp_city', 'ktp_city_id', 'ktp_province_id');

        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('religion', 'religion_name');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('religion_name', 'religion');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('religion_id');
            $table->dropConstrainedForeignId('marital_status_id');
            $table->dropConstrainedForeignId('blood_type_id');
            $table->dropConstrainedForeignId('employee_type_id');
            $table->dropConstrainedForeignId('domicile_province_id');
            $table->dropConstrainedForeignId('domicile_city_id');
            $table->dropConstrainedForeignId('ktp_province_id');
            $table->dropConstrainedForeignId('ktp_city_id');
        });
    }

    private function backfillByName(string $fkColumn, string $masterTable, string $legacyColumn): void
    {
        foreach (DB::table($masterTable)->get(['id', 'name']) as $master) {
            DB::table('employees')
                ->whereNull($fkColumn)
                ->whereRaw('LOWER(TRIM(' . $legacyColumn . ')) = ?', [mb_strtolower(trim($master->name))])
                ->update([$fkColumn => $master->id]);
        }
    }

    private function backfillByLegacyKey(string $fkColumn, string $masterTable, string $legacyColumn): void
    {
        foreach (DB::table($masterTable)->whereNotNull('legacy_key')->get(['id', 'legacy_key']) as $master) {
            DB::table('employees')
                ->whereNull($fkColumn)
                ->where($legacyColumn, $master->legacy_key)
                ->update([$fkColumn => $master->id]);
        }
    }

    private function backfillEmployeeType(): void
    {
        foreach (DB::table('employee_types')->whereNotNull('legacy_key')->get(['id', 'legacy_key']) as $master) {
            DB::table('employees')
                ->whereNull('employee_type_id')
                ->where('employment_status', $master->legacy_key)
                ->update(['employee_type_id' => $master->id]);
        }
    }

    private function backfillCity(string $legacyColumn, string $cityFk, string $provinceFk): void
    {
        if (! Schema::hasTable('cities') || DB::table('cities')->doesntExist()) {
            return;
        }

        foreach (DB::table('cities')->get(['id', 'province_id', 'name']) as $city) {
            $needle = mb_strtolower(trim($city->name));

            DB::table('employees')
                ->whereNull($cityFk)
                ->whereRaw('LOWER(TRIM(' . $legacyColumn . ')) = ?', [$needle])
                ->update([$cityFk => $city->id, $provinceFk => $city->province_id]);

            // "Kota Bandung" / "Kab. Bogor" — cocokkan tanpa prefiks juga.
            DB::table('employees')
                ->whereNull($cityFk)
                ->whereRaw('LOWER(TRIM(' . $legacyColumn . ')) IN (?, ?)', ['kota ' . $needle, 'kab. ' . $needle])
                ->update([$cityFk => $city->id, $provinceFk => $city->province_id]);
        }
    }
};
