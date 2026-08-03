<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('department')->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('position')->constrained()->nullOnDelete();
        });

        $usedCodes = [];
        $makeCode = function (string $name) use (&$usedCodes): string {
            $words = preg_split('/\s+/', trim($name));
            $code  = Str::upper(Str::substr(implode('', array_map(fn ($w) => Str::substr($w, 0, 1), $words)), 0, 6));
            $code  = $code !== '' ? $code : Str::upper(Str::substr($name, 0, 6));
            $base  = $code;
            $i     = 1;
            while (in_array($code, $usedCodes, true)) {
                $code = $base . $i;
                $i++;
            }
            $usedCodes[] = $code;

            return $code;
        };

        // Backfill departments dari nilai teks bebas yang ada
        $departmentMap = [];
        $names = DB::table('employees')->whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department');
        foreach ($names as $name) {
            $id = DB::table('departments')->insertGetId([
                'code'       => $makeCode($name),
                'name'       => $name,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $departmentMap[$name] = $id;
        }
        foreach ($departmentMap as $name => $id) {
            DB::table('employees')->where('department', $name)->update(['department_id' => $id]);
        }

        // Backfill positions dari nilai teks bebas yang ada
        $usedCodes = [];
        $positionMap = [];
        $names = DB::table('employees')->whereNotNull('position')->where('position', '!=', '')->distinct()->pluck('position');
        foreach ($names as $name) {
            $id = DB::table('positions')->insertGetId([
                'code'       => $makeCode($name),
                'name'       => $name,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $positionMap[$name] = $id;
        }
        foreach ($positionMap as $name => $id) {
            DB::table('employees')->where('position', $name)->update(['position_id' => $id]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['department', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('position')->nullable();
        });

        DB::table('employees')->orderBy('id')->each(function ($employee) {
            $department = $employee->department_id ? DB::table('departments')->find($employee->department_id) : null;
            $position   = $employee->position_id ? DB::table('positions')->find($employee->position_id) : null;

            DB::table('employees')->where('id', $employee->id)->update([
                'department' => $department?->name,
                'position'   => $position?->name,
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('position_id');
        });
    }
};
