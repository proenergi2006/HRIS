<?php

namespace App\Models\Appraisal;

use Illuminate\Database\Eloquent\Model;

class AppraisalFlowConfig extends Model
{
    protected $fillable = ['department', 'step', 'role', 'label'];

    protected $casts = ['step' => 'integer'];

    /**
     * Find the flow config for a given department and step.
     * Falls back to default (department = null) if no specific config exists.
     */
    public static function forDepartment(?string $department, int $step): ?self
    {
        if ($department) {
            $config = static::where('department', $department)->where('step', $step)->first();
            if ($config) {
                return $config;
            }
        }

        return static::whereNull('department')->where('step', $step)->first();
    }
}
