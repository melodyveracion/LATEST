<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fund labels may repeat across colleges (e.g. "Compre") — uniqueness is per department.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fund_sources')) {
            return;
        }

        $this->dropGlobalNameUniqueIndex();

        Schema::table('fund_sources', function (Blueprint $table) {
            $table->unique(['department_unit_id', 'name'], 'fund_sources_department_unit_name_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('fund_sources')) {
            return;
        }

        Schema::table('fund_sources', function (Blueprint $table) {
            $table->dropUnique('fund_sources_department_unit_name_unique');
        });

        Schema::table('fund_sources', function (Blueprint $table) {
            $table->unique('name', 'fund_sources_name_unique');
        });
    }

    private function dropGlobalNameUniqueIndex(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $db = DB::getDatabaseName();
            $rows = DB::select(
                "SELECT INDEX_NAME AS n FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'fund_sources' AND NON_UNIQUE = 0 AND INDEX_NAME != 'PRIMARY'
                 GROUP BY INDEX_NAME
                 HAVING COUNT(*) = 1 AND MAX(COLUMN_NAME) = 'name'",
                [$db]
            );
            if (!empty($rows) && isset($rows[0]->n) && $rows[0]->n !== '') {
                DB::statement('ALTER TABLE `fund_sources` DROP INDEX `'.$rows[0]->n.'`');

                return;
            }
        }

        Schema::table('fund_sources', function (Blueprint $table) {
            try {
                $table->dropUnique(['name']);
            } catch (\Throwable) {
                try {
                    $table->dropUnique('fund_sources_name_unique');
                } catch (\Throwable) {
                    // No global unique on name; safe to continue
                }
            }
        });
    }
};
