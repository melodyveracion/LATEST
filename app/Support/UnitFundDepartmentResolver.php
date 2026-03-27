<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Legacy department_units rows often have no fund_sources; seeded funds attach to canonical names.
 */
final class UnitFundDepartmentResolver
{
    /**
     * Exact legacy name => canonical department_units.name (must match reference data / DB).
     *
     * @var array<string, string>
     */
    private const LEGACY_NAME_TO_CANONICAL = [
        'College of Arts and Science' => 'College of Arts and Sciences(CAS)',
        'College of Business Administration and Accountancy' => 'College of Business Administration and Accountancy(CBAA)',
        'College of Health Science' => 'College of Health Sciences(CHS)',
        'College of Public Administration' => 'College of Public Administration(CPAD)',
        'College of Communication and Information Technology' => 'College of Communication and Information Technology(CCIT)',
        'College of Fine Arts and Design' => 'College of Fine Arts and Design(CFAD)',
        'College of Criminal Justice Education' => 'College of Criminal Justice Education(CCJE)',
        'College of Hospitality and Tourism Management' => 'College of Hospitality and Tourism Management(CHTM)',
        'College of Medicine' => 'College of Medicine(CMED)',
        'College of Nursing' => 'College of Nursing(CN)',
        'College of Social Work' => 'College of Social Work(CSW)',
        'College of Teacher Education' => 'College of Teacher Education(CTE)',
        'College of Technology' => 'College of Technology(CTECH)',
        'College of Architecture' => 'College of Architecture(CARCH)',
        'College of Law' => 'College of Law(CLAW)',
        'College of Engineering' => 'College of Engineering(COE)',
        'College of Engineering (CENGR)' => 'College of Engineering(COE)',
        'College of Engineering (COE)' => 'College of Engineering(COE)',
        // Spaced-paren renames (pre–reference-format)
        'College of Arts & Sciences (CAS)' => 'College of Arts and Sciences(CAS)',
        'College of Business Administration & Accountancy (CBAA)' => 'College of Business Administration and Accountancy(CBAA)',
        'College of Communication & Information Technology (CCIT)' => 'College of Communication and Information Technology(CCIT)',
        'College of Fine Arts & Design (CFAD)' => 'College of Fine Arts and Design(CFAD)',
        'College of Hospitality & Tourism Management (CHTM)' => 'College of Hospitality and Tourism Management(CHTM)',
        'College of Public Administration & Governance (CPAD)' => 'College of Public Administration(CPAD)',
        'College of Medicine (CMed)' => 'College of Medicine(CMED)',
        'College of Criminal Justice Education (CCJE)' => 'College of Criminal Justice Education(CCJE)',
        'College of Health Sciences (CHS)' => 'College of Health Sciences(CHS)',
        'College of Law (CLAW)' => 'College of Law(CLAW)',
        'College of Nursing (CN)' => 'College of Nursing(CN)',
        'College of Social Work (CSW)' => 'College of Social Work(CSW)',
        'College of Teacher Education (CTE)' => 'College of Teacher Education(CTE)',
        'College of Technology (CTECH)' => 'College of Technology(CTECH)',
        'College of Architecture (CARCH)' => 'College of Architecture(CARCH)',
    ];

    public static function resolve(?int $departmentUnitId): ?int
    {
        if (!$departmentUnitId) {
            return null;
        }
        if (DB::table('fund_sources')->where('department_unit_id', $departmentUnitId)->exists()) {
            return $departmentUnitId;
        }
        $name = DB::table('department_units')->where('department_unit_id', $departmentUnitId)->value('name');
        if ($name === null || $name === '') {
            return null;
        }
        if (isset(self::LEGACY_NAME_TO_CANONICAL[$name])) {
            $canonicalId = DB::table('department_units')
                ->where('name', self::LEGACY_NAME_TO_CANONICAL[$name])
                ->value('department_unit_id');
            if ($canonicalId && DB::table('fund_sources')->where('department_unit_id', $canonicalId)->exists()) {
                return (int) $canonicalId;
            }
        }

        $suffixId = DB::table('department_units as du')
            ->where(function ($q) use ($name) {
                $q->where('du.name', 'like', $name.' (%')
                    ->orWhere('du.name', 'like', $name.'(%');
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('fund_sources as fs')
                    ->whereColumn('fs.department_unit_id', 'du.department_unit_id');
            })
            ->orderBy('du.department_unit_id')
            ->value('du.department_unit_id');

        return $suffixId ? (int) $suffixId : null;
    }

    public static function resolvedDepartmentId(?int $departmentUnitId): ?int
    {
        return self::resolve($departmentUnitId);
    }

    public static function resolvedDepartmentName(?int $departmentUnitId): ?string
    {
        $resolvedId = self::resolve($departmentUnitId);

        if (!$resolvedId) {
            return null;
        }

        return DB::table('department_units')
            ->where('department_unit_id', $resolvedId)
            ->value('name');
    }

    public static function fundSourcesForDepartment(?int $departmentUnitId): Collection
    {
        $resolvedId = self::resolve($departmentUnitId);

        if (!$resolvedId) {
            return collect();
        }

        return DB::table('fund_sources')
            ->selectRaw('fund_src_id as id, fund_src_id, name, department_unit_id')
            ->where('department_unit_id', $resolvedId)
            ->orderBy('name')
            ->get();
    }

    public static function findFundSourceForDepartment(?int $departmentUnitId, ?int $fundSourceId): ?object
    {
        if (!$fundSourceId) {
            return null;
        }

        return self::fundSourcesForDepartment($departmentUnitId)
            ->first(fn ($fundSource) => (int) $fundSource->id === (int) $fundSourceId);
    }
}
