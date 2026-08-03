<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Level;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmployeeImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;

    /** @var array<int, array{row:int,message:string}> */
    public array $errors = [];

    private array $departmentCodeCache = [];
    private array $positionCodeCache = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $rowNum = $i + 2; // baris 1 = heading

            try {
                $this->importRow($row);
            } catch (\Throwable $e) {
                $this->errors[] = ['row' => $rowNum, 'message' => $e->getMessage()];
            }
        }
    }

    private function importRow(Collection $row): void
    {
        $name = $this->str($row['nama'] ?? null);
        if (! $name) {
            throw new \RuntimeException('Kolom Nama wajib diisi.');
        }

        $nip = $this->str($row['nip'] ?? null);

        $employee = $nip ? Employee::where('nip', $nip)->first() : null;
        $isNew    = ! $employee;
        $employee ??= new Employee();

        $companyId = $this->resolveCompanyId($row['kode_perusahaan'] ?? null);

        $employee->fill([
            'name'                       => $name,
            'nip'                        => $nip ?: $employee->nip,
            'company_id'                 => $companyId,
            'department_id'              => $this->resolveDepartmentId($row['departemen'] ?? null, $companyId),
            'position_id'                => $this->resolvePositionId($row['jabatan'] ?? null, $companyId),
            'lob'                        => $this->str($row['lob'] ?? null),
            'level_id'                   => $this->resolveLevelId($row['level_jabatan'] ?? null),
            'manager_id'                 => $this->resolveManagerId($row['nip_atasan'] ?? null),
            'start_date'                 => $this->date($row['tanggal_mulai_kerja'] ?? null),
            'employment_status'          => $this->resolveEmploymentStatus($row['status_kepegawaian'] ?? null),
            'contract_end_date'          => $this->date($row['tanggal_kontrak_berakhir'] ?? null),
            'is_active'                  => $this->bool($row['status_aktif'] ?? null, true),

            'gender'                     => $this->resolveGender($row['jenis_kelamin'] ?? null),
            'birth_place'                => $this->str($row['tempat_lahir'] ?? null),
            'birth_date'                 => $this->date($row['tanggal_lahir'] ?? null),
            'ktp_number'                 => $this->str($row['no_ktp'] ?? null),
            'npwp_number'                => $this->str($row['npwp'] ?? null),
            'npwp_city'                  => $this->str($row['kota_npwp'] ?? null),
            'npwp_date'                  => $this->date($row['tanggal_npwp'] ?? null),
            'marital_status'             => $this->resolveMaritalStatus($row['status_kawin'] ?? null),
            'religion'                   => $this->str($row['agama'] ?? null),
            'blood_type'                 => $this->resolveBloodType($row['golongan_darah'] ?? null),
            'employee_type'              => $this->resolveEmployeeType($row['tipe_karyawan'] ?? null),
            'finger_id'                  => $this->str($row['finger_id'] ?? null),

            'email'                      => $this->str($row['email'] ?? null),
            'phone'                      => $this->str($row['no_hp'] ?? null),
            'home_phone'                 => $this->str($row['no_telp_rumah'] ?? null),

            'domicile_address'           => $this->str($row['alamat_domisili'] ?? null),
            'domicile_city'              => $this->str($row['kota_domisili'] ?? null),
            'domicile_district'          => $this->str($row['kecamatan_domisili'] ?? null),
            'domicile_subdistrict'       => $this->str($row['kelurahan_domisili'] ?? null),

            'ktp_address'                => $this->str($row['alamat_ktp'] ?? null),
            'ktp_city'                   => $this->str($row['kota_ktp'] ?? null),
            'ktp_district'               => $this->str($row['kecamatan_ktp'] ?? null),
            'ktp_subdistrict'            => $this->str($row['kelurahan_ktp'] ?? null),

            'emergency_contact_name'     => $this->str($row['nama_kontak_darurat'] ?? null),
            'emergency_contact_relation' => $this->str($row['hubungan_kontak_darurat'] ?? null),
            'emergency_contact_phone'    => $this->str($row['no_telp_kontak_darurat'] ?? null),
        ]);

        $employee->save();

        $isNew ? $this->created++ : $this->updated++;
    }

    // ── Resolvers ────────────────────────────────────────────────────

    private function resolveCompanyId(mixed $value): ?int
    {
        $code = $this->str($value);
        if (! $code) {
            return null;
        }

        return Company::whereRaw('LOWER(code) = ?', [Str::lower($code)])->value('id');
    }

    private function resolveDepartmentId(mixed $value, ?int $companyId): ?int
    {
        $name = $this->str($value);
        if (! $name) {
            return null;
        }

        $cacheKey = Str::lower($name) . '|' . $companyId;
        if (isset($this->departmentCodeCache[$cacheKey])) {
            return $this->departmentCodeCache[$cacheKey];
        }

        $department = Department::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
        if (! $department) {
            $department = Department::create([
                'company_id' => $companyId,
                'code'       => $this->generateCode($name, Department::class),
                'name'       => $name,
                'is_active'  => true,
            ]);
        }

        return $this->departmentCodeCache[$cacheKey] = $department->id;
    }

    private function resolvePositionId(mixed $value, ?int $companyId): ?int
    {
        $name = $this->str($value);
        if (! $name) {
            return null;
        }

        $cacheKey = Str::lower($name) . '|' . $companyId;
        if (isset($this->positionCodeCache[$cacheKey])) {
            return $this->positionCodeCache[$cacheKey];
        }

        $position = Position::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
        if (! $position) {
            $position = Position::create([
                'company_id' => $companyId,
                'code'       => $this->generateCode($name, Position::class),
                'name'       => $name,
                'is_active'  => true,
            ]);
        }

        return $this->positionCodeCache[$cacheKey] = $position->id;
    }

    private function resolveLevelId(mixed $value): ?int
    {
        $name = $this->str($value);
        if (! $name) {
            return null;
        }

        return Level::whereRaw('LOWER(name) = ?', [Str::lower($name)])->value('id');
    }

    private function resolveManagerId(mixed $value): ?int
    {
        $nip = $this->str($value);
        if (! $nip) {
            return null;
        }

        return Employee::where('nip', $nip)->value('id');
    }

    private function resolveEmploymentStatus(mixed $value): string
    {
        $v = Str::lower(trim((string) $value));

        return match (true) {
            str_contains($v, 'kontrak')   => 'contract',
            str_contains($v, 'probation') => 'probation',
            default                       => 'permanent',
        };
    }

    private function resolveGender(mixed $value): ?string
    {
        $v = Str::lower(trim((string) $value));

        return match (true) {
            in_array($v, ['l', 'laki-laki', 'pria', 'male'], true)     => 'L',
            in_array($v, ['p', 'perempuan', 'wanita', 'female'], true) => 'P',
            default                                                    => null,
        };
    }

    private function resolveMaritalStatus(mixed $value): ?string
    {
        $v = Str::lower(trim((string) $value));

        return match (true) {
            str_contains($v, 'belum')       => 'belum_kawin',
            str_contains($v, 'cerai hidup') => 'cerai_hidup',
            str_contains($v, 'cerai mati')  => 'cerai_mati',
            str_contains($v, 'kawin') || str_contains($v, 'menikah') => 'kawin',
            default => null,
        };
    }

    private function resolveBloodType(mixed $value): ?string
    {
        $v = Str::upper(trim((string) $value));

        return in_array($v, ['A', 'B', 'AB', 'O'], true) ? $v : null;
    }

    private function resolveEmployeeType(mixed $value): string
    {
        $v = Str::lower(trim((string) $value));

        return $v === 'expat' ? 'expat' : 'local';
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function str(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function bool(mixed $value, bool $default): bool
    {
        $v = Str::lower(trim((string) $value));
        if ($v === '') {
            return $default;
        }

        return in_array($v, ['ya', 'aktif', '1', 'true', 'yes'], true);
    }

    private function date(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function generateCode(string $name, string $model): string
    {
        $words = preg_split('/\s+/', trim($name));
        $code  = Str::upper(Str::substr(implode('', array_map(fn ($w) => Str::substr($w, 0, 1), $words)), 0, 6));
        $code  = $code !== '' ? $code : Str::upper(Str::substr($name, 0, 6));

        $base = $code;
        $i    = 1;
        while ($model::where('code', $code)->exists()) {
            $code = $base . $i;
            $i++;
        }

        return $code;
    }
}
