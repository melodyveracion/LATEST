<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** ERD primary key column names per table */
    private array $pkRenames = [
        'users' => 'user_id',
        'department_units' => 'department_unit_id',
        'fund_sources' => 'fund_src_id',
        'ppmps' => 'ppmp_id',
        'purchase_requests' => 'pr_id',
        'ppmp_items' => 'ppmp_item_id',
        'categories' => 'category_id',
        'preset_items' => 'project_id',
        'consolidated_items' => 'consol_item_id',
        'consolidated_item_sources' => 'consol_item_src_id',
        'purchase_request_items' => 'pr_item_id',
        'biddings' => 'bidding_id',
        'deliveries' => 'delivery_id',
        'notifications' => 'notification_id',
        'inventories' => 'inventory_id',
    ];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach ($this->pkRenames as $table => $newPkName) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
                continue;
            }

            if ($driver === 'mysql') {
                $this->renamePrimaryKeyMySQL($table, $newPkName);
            } else {
                Schema::table($table, function (Blueprint $t) use ($newPkName) {
                    $t->renameColumn('id', $newPkName);
                });
            }
        }
    }

    private function renamePrimaryKeyMySQL(string $table, string $newPkName): void
    {
        $this->dropForeignKeysReferencingTable($table);
        // MySQL: remove AUTO_INCREMENT before dropping primary key (required by MySQL)
        DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL");
        if ($this->tableHasPrimaryKeyOnColumn($table, 'id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropPrimary(['id']);
            });
        }
        $canAddPrimary = $this->tableHasNoDuplicateIds($table);
        if ($canAddPrimary) {
            DB::statement("ALTER TABLE `{$table}` CHANGE `id` `{$newPkName}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
        } else {
            DB::statement("ALTER TABLE `{$table}` CHANGE `id` `{$newPkName}` BIGINT UNSIGNED NOT NULL");
        }
        $this->restoreForeignKeysForTable($table, $newPkName);
    }

    /** Return true if the table has no duplicate values in the id column (so we can safely add PRIMARY KEY). */
    private function tableHasNoDuplicateIds(string $table): bool
    {
        $result = DB::selectOne("SELECT COUNT(*) AS cnt FROM (SELECT `id` FROM `{$table}` GROUP BY `id` HAVING COUNT(*) > 1) t");
        return ($result->cnt ?? 0) === 0;
    }

    /** Return true if the table has a primary key on the given column. */
    private function tableHasPrimaryKeyOnColumn(string $table, string $column): bool
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            "SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = 'PRIMARY'",
            [$db, $table]
        );
        return count($rows) === 1 && ($rows[0]->COLUMN_NAME ?? '') === $column;
    }

    /** Drop all foreign keys that reference the given table (by querying information_schema). */
    private function dropForeignKeysReferencingTable(string $referencedTable): void
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            "SELECT DISTINCT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = 'id'",
            [$db, $referencedTable]
        );
        foreach ($rows as $row) {
            try {
                DB::statement("ALTER TABLE `{$row->TABLE_NAME}` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    /** Re-create foreign keys for tables that reference the given table (using new PK column name). */
    private function restoreForeignKeysForTable(string $table, string $newPkName): void
    {
        $refs = $this->getTablesReferencing($table);
        foreach ($refs as $childTable => $columns) {
            if (!Schema::hasTable($childTable)) {
                continue;
            }
            foreach ($columns as $column) {
                if (!Schema::hasColumn($childTable, $column)) {
                    continue;
                }
                try {
                    Schema::table($childTable, function (Blueprint $t) use ($table, $column, $newPkName) {
                        $t->foreign($column)->references($newPkName)->on($table)->cascadeOnDelete();
                    });
                } catch (\Throwable $e) {
                    // ignore if already exists or other error
                }
            }
        }
    }

    private function getTablesReferencing(string $table): array
    {
        $map = [
            'users' => ['sessions' => ['user_id'], 'ppmps' => ['user_id'], 'purchase_requests' => ['user_id'], 'notifications' => ['user_id']],
            'department_units' => ['users' => ['department_unit_id'], 'fund_sources' => ['department_unit_id'], 'ppmps' => ['department_unit_id']],
            'fund_sources' => ['users' => ['fund_source_id'], 'ppmps' => ['fund_source_id']],
            'categories' => ['ppmp_items' => ['category_id'], 'preset_items' => ['category_id'], 'consolidated_items' => ['category_id']],
            'ppmps' => ['ppmp_items' => ['ppmp_id'], 'purchase_requests' => ['ppmp_id']],
            'ppmp_items' => ['purchase_request_items' => ['ppmp_item_id']],
            'purchase_requests' => ['purchase_request_items' => ['purchase_request_id'], 'consolidated_item_sources' => ['purchase_request_id'], 'deliveries' => ['purchase_request_id']],
            'consolidated_items' => ['consolidated_item_sources' => ['consolidated_item_id'], 'biddings' => ['consolidated_item_id'], 'deliveries' => ['consolidated_item_id']],
            'deliveries' => ['inventories' => ['last_delivery_id']],
        ];
        return $map[$table] ?? [];
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $renames = array_reverse($this->pkRenames, true);

        foreach ($renames as $table => $pkName) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $pkName)) {
                continue;
            }

            if ($driver === 'mysql') {
                $this->revertPrimaryKeyMySQL($table, $pkName);
            } else {
                Schema::table($table, function (Blueprint $t) use ($pkName) {
                    $t->renameColumn($pkName, 'id');
                });
            }
        }
    }

    private function revertPrimaryKeyMySQL(string $table, string $currentPkName): void
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            "SELECT DISTINCT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?",
            [$db, $table, $currentPkName]
        );
        foreach ($rows as $row) {
            try {
                DB::statement("ALTER TABLE `{$row->TABLE_NAME}` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
            } catch (\Throwable $e) {
                // ignore
            }
        }
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$currentPkName}` BIGINT UNSIGNED NOT NULL");
        if ($this->tableHasPrimaryKeyOnColumn($table, $currentPkName)) {
            Schema::table($table, function (Blueprint $t) use ($currentPkName) {
                $t->dropPrimary([$currentPkName]);
            });
        }
        DB::statement("ALTER TABLE `{$table}` CHANGE `{$currentPkName}` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
        foreach ($refs as $childTable => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasTable($childTable) && Schema::hasColumn($childTable, $column)) {
                    Schema::table($childTable, function (Blueprint $t) use ($table, $column) {
                        $t->foreign($column)->references('id')->on($table)->cascadeOnDelete();
                    });
                }
            }
        }
    }
};
