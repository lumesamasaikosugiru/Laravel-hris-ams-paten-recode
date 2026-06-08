<?php
namespace App\Services;

use App\Models\Employee;

class NipyGenerator
{
    private const EDUCATION_CODES = [
        'sd'=>'01','smp'=>'02','sma'=>'03','d3'=>'06','s1'=>'07','s2'=>'08','s3'=>'09',
    ];

    public static function getEducationCode(?string $edu): string {
        return self::EDUCATION_CODES[$edu] ?? '03';
    }

    public static function getEmploymentCode(bool $isGuru, string $type): string {
        return $isGuru ? ($type==='permanent'?'11':'12') : ($type==='permanent'?'21':'22');
    }

    public static function generate(Employee $employee): string {
        $yearCode = $employee->join_date->format('y');
        $eduCode  = self::getEducationCode($employee->last_education);
        $empCode  = self::getEmploymentCode($employee->is_guru, $employee->employee_type);
        $prefix   = $yearCode . $eduCode . $empCode;

        $lastNipy = Employee::where('nipy', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(nipy, 7) AS UNSIGNED) DESC')
            ->value('nipy');

        $next = $lastNipy ? (int)substr($lastNipy, 6) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public static function generateTemporaryNik(): string {
        $date = now()->format('Ymd');
        $last = Employee::where('nik', 'like', "TMP-{$date}-%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(nik, '-', -1) AS UNSIGNED) DESC")
            ->value('nik');
        $next = $last ? (int)substr($last, strrpos($last, '-') + 1) + 1 : 1;
        return "TMP-{$date}-" . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public static function calculateProbationEndDate(\Carbon\Carbon $start, bool $isGuru): \Carbon\Carbon {
        return $isGuru ? $start->copy()->addMonths(6) : $start->copy()->addMonths(3);
    }
}
