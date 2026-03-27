<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_request_items') || !Schema::hasColumn('purchase_request_items', 'id')) {
            return;
        }

        $hasPrimaryKey = !empty(DB::select("SHOW INDEX FROM `purchase_request_items` WHERE Key_name = 'PRIMARY'"));

        if (! $hasPrimaryKey) {
            DB::statement('ALTER TABLE `purchase_request_items` ADD PRIMARY KEY (`id`)');
        }

        DB::statement('ALTER TABLE `purchase_request_items` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_request_items') || !Schema::hasColumn('purchase_request_items', 'id')) {
            return;
        }

        DB::statement('ALTER TABLE `purchase_request_items` MODIFY `id` BIGINT UNSIGNED NOT NULL');

        $hasPrimaryKey = !empty(DB::select("SHOW INDEX FROM `purchase_request_items` WHERE Key_name = 'PRIMARY'"));

        if ($hasPrimaryKey) {
            DB::statement('ALTER TABLE `purchase_request_items` DROP PRIMARY KEY');
        }
    }
};
