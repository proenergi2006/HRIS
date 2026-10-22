<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeHistory;

class EmployeeHistoryLogger
{
    public static function record(
        Employee|int $employee,
        string $category,
        ?string $old,
        ?string $new,
        $effectiveDate = null,
        ?string $note = null,
    ): ?EmployeeHistory {
        if ((string) $old === (string) $new) {
            return null;
        }

        return EmployeeHistory::create([
            'employee_id'        => $employee instanceof Employee ? $employee->id : $employee,
            'category'           => $category,
            'effective_date'     => $effectiveDate ?: now()->toDateString(),
            'old_value'          => $old,
            'new_value'          => $new,
            'note'               => $note,
            'changed_by_user_id' => auth()->id(),
        ]);
    }

    /** Label kepegawaian karyawan saat ini, per kategori — dibaca sebelum & sesudah update. */
    public static function snapshot(Employee $employee): array
    {
        $employee->loadMissing(['position', 'level', 'department']);

        return [
            'title'           => $employee->position?->name,
            'grade'           => $employee->level?->name,
            'cost_center'     => $employee->department
                ? trim(($employee->department->cost_center ? $employee->department->cost_center . ' — ' : '') . $employee->department->name)
                : null,
            'employee_status' => $employee->employment_status_label,
        ];
    }

    /** Bandingkan snapshot lama vs baru lalu catat kategori yang berubah. */
    public static function recordChanges(Employee $employee, array $before, array $after, $effectiveDate = null): void
    {
        foreach ($after as $category => $new) {
            self::record($employee, $category, $before[$category] ?? null, $new, $effectiveDate);
        }
    }
}
