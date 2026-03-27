<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Syncs only `fund_sources` from database/data/university_reference_data.php.
 * Does not insert or update `department_units` (departments must already exist with matching `name`).
 *
 * Run: php artisan db:seed --class=FundSourcesOnlySeeder
 */
class FundSourcesOnlySeeder extends Seeder
{
    private const DEFAULT_OPERATING_FUND = 'General / Operating Fund';

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

        /** @var list<array{name: string, abbreviation: ?string, funds: list<string>}> $rows */
        $rows = require $path;

        /** @var array<int, list<string>> */
        $expectedFundNamesByDeptId = [];
        /** @var list<string> */
        $skippedNames = [];

        foreach ($rows as $row) {
            $name = $row['name'];
            $existing = DB::table('department_units')->where('name', $name)->first();
            if (!$existing) {
                $skippedNames[] = $name;

                continue;
            }

            $deptId = (int) $existing->department_unit_id;
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

        $syncedDepts = count($expectedFundNamesByDeptId);
        $this->command?->info("Fund sources only: {$syncedDepts} department(s) updated, {$removed} obsolete row(s) removed.");
        if ($skippedNames !== []) {
            $n = count($skippedNames);
            $sample = implode('", "', array_slice($skippedNames, 0, 5));
            $more = $n > 5 ? ' (+' . ($n - 5) . ' more)' : '';
            $this->command?->warn("Skipped {$n} reference row(s): no department_unit named like \"{$sample}\"{$more}. Run UniversityReferenceDataSeeder once to create missing departments.");
        }
    }
}
