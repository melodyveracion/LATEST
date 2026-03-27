<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Upserts department/office rows and their fund sources from database/data/university_reference_data.php.
 *
 * Fund names are stored as in the data file. The same label may exist under different colleges when the
 * DB uses a composite unique index on (department_unit_id, name).
 *
 * After sync, fund_sources for each listed department that are not in the reference file for that
 * department are deleted. If rows are still referenced and the DB forbids delete, run
 * `php artisan university:reset-fund-sources --force` or clear those references first.
 *
 * Run: php artisan db:seed --class=UniversityReferenceDataSeeder
 */
class UniversityReferenceDataSeeder extends Seeder
{
    private const DEFAULT_OPERATING_FUND = 'General / Operating Fund';

    /** Used on insert when `department_units.budget` is NOT NULL in the database. */
    private const DEFAULT_DEPARTMENT_BUDGET = '0.00';

    /**
     * Preserve department_unit_id when the canonical name/abbreviation in the data file changes.
     *
     * @var array<string, array{name: string, abbreviation: ?string}>
     */
    private const DEPARTMENT_RENAMES_FROM_PREVIOUS = [
        'College of Engineering (COE)' => ['name' => 'College of Engineering(COE)', 'abbreviation' => 'COE'],
        'College of Engineering (COE)' => ['name' => 'College of Engineering(COE)', 'abbreviation' => 'COE'],
        'College of Business Administration & Accountancy (CBAA)' => ['name' => 'College of Business Administration and Accountancy(CBAA)', 'abbreviation' => 'CBAA'],
        'College of Arts & Sciences (CAS)' => ['name' => 'College of Arts and Sciences(CAS)', 'abbreviation' => 'CAS'],
        'College of Communication & Information Technology (CCIT)' => ['name' => 'College of Communication and Information Technology(CCIT)', 'abbreviation' => 'CCIT'],
        'College of Criminal Justice Education (CCJE)' => ['name' => 'College of Criminal Justice Education(CCJE)', 'abbreviation' => 'CCJE'],
        'College of Fine Arts & Design (CFAD)' => ['name' => 'College of Fine Arts and Design(CFAD)', 'abbreviation' => 'CFAD'],
        'College of Health Sciences (CHS)' => ['name' => 'College of Health Sciences(CHS)', 'abbreviation' => 'CHS'],
        'College of Hospitality & Tourism Management (CHTM)' => ['name' => 'College of Hospitality and Tourism Management(CHTM)', 'abbreviation' => 'CHTM'],
        'College of Law (CLAW)' => ['name' => 'College of Law(CLAW)', 'abbreviation' => 'CLAW'],
        'College of Medicine (CMed)' => ['name' => 'College of Medicine(CMED)', 'abbreviation' => 'CMED'],
        'College of Nursing (CN)' => ['name' => 'College of Nursing(CN)', 'abbreviation' => 'CN'],
        'College of Public Administration & Governance (CPAD)' => ['name' => 'College of Public Administration(CPAD)', 'abbreviation' => 'CPAD'],
        'College of Social Work (CSW)' => ['name' => 'College of Social Work(CSW)', 'abbreviation' => 'CSW'],
        'College of Teacher Education (CTE)' => ['name' => 'College of Teacher Education(CTE)', 'abbreviation' => 'CTE'],
        'College of Technology (CTECH)' => ['name' => 'College of Technology(CTECH)', 'abbreviation' => 'CTECH'],
        'College of Architecture (CARCH)' => ['name' => 'College of Architecture(CARCH)', 'abbreviation' => 'CARCH'],
        'Quality Management Office - ISO' => ['name' => 'Quality Management Office- ISO', 'abbreviation' => 'QMO'],
        'National Service Training Program Office' => ['name' => 'National Service Training Porgram Office', 'abbreviation' => 'NSTP'],
    ];

    public function run(): void
    {
        if (!Schema::hasTable('department_units') || !Schema::hasTable('fund_sources')) {
            $this->command?->error('Tables department_units and/or fund_sources are missing. Run migrations first.');

            return;
        }

        $path = database_path('data/university_reference_data.php');
        if (!is_file($path)) {
            $this->command?->error('Missing data file: database/data/university_reference_data.php');

            return;
        }

        foreach (self::DEPARTMENT_RENAMES_FROM_PREVIOUS as $oldName => $new) {
            $row = DB::table('department_units')->where('name', $oldName)->first();
            if ($row) {
                DB::table('department_units')
                    ->where('department_unit_id', $row->department_unit_id)
                    ->update([
                        'name' => $new['name'],
                        'abbreviation' => $new['abbreviation'],
                    ]);
            }
        }

        /** @var list<array{name: string, abbreviation: ?string, funds: list<string>}> $rows */
        $rows = require $path;

        /** @var array<int, list<string>> */
        $expectedFundNamesByDeptId = [];

        foreach ($rows as $row) {
            $name = $row['name'];
            $abbr = isset($row['abbreviation']) ? trim((string) $row['abbreviation']) : null;
            if ($abbr === '') {
                $abbr = null;
            }
            if ($abbr !== null && strlen($abbr) > 20) {
                $abbr = substr($abbr, 0, 20);
            }

            $existing = DB::table('department_units')->where('name', $name)->first();
            if ($existing) {
                DB::table('department_units')
                    ->where('department_unit_id', $existing->department_unit_id)
                    ->update(['abbreviation' => $abbr]);
                $deptId = (int) $existing->department_unit_id;
            } else {
                $deptId = (int) DB::table('department_units')->insertGetId([
                    'name' => $name,
                    'abbreviation' => $abbr,
                    'budget' => self::DEFAULT_DEPARTMENT_BUDGET,
                ]);
            }

            if (!$deptId) {
                continue;
            }

            $funds = $row['funds'] ?? [];
            if ($funds === []) {
                $funds = [self::DEFAULT_OPERATING_FUND];
            }

            $expectedNames = [];
            foreach ($funds as $rawFund) {
                $fundName = trim((string) $rawFund);
                if ($fundName === '') {
                    continue;
                }
                $expectedNames[] = $fundName;
                DB::table('fund_sources')->updateOrInsert(
                    [
                        'department_unit_id' => $deptId,
                        'name' => $fundName,
                    ],
                    []
                );
            }

            if ($expectedNames === []) {
                $expectedNames = [self::DEFAULT_OPERATING_FUND];
                DB::table('fund_sources')->updateOrInsert(
                    [
                        'department_unit_id' => $deptId,
                        'name' => self::DEFAULT_OPERATING_FUND,
                    ],
                    []
                );
            }

            $expectedFundNamesByDeptId[$deptId] = $expectedNames;
        }

        $removed = 0;
        foreach ($expectedFundNamesByDeptId as $deptId => $expectedNames) {
            $removed += DB::table('fund_sources')
                ->where('department_unit_id', $deptId)
                ->whereNotIn('name', $expectedNames)
                ->delete();
        }

        $this->command?->info('University reference data synced: ' . count($rows) . ' department/office records; removed ' . $removed . ' obsolete fund source row(s) no longer in the reference file.');
    }
}
