<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ppmps') || !Schema::hasColumn('ppmps', 'status')) {
            return;
        }

        DB::statement(
            "ALTER TABLE `ppmps` MODIFY `status` ENUM('Draft','Submitted','Approved','Disapproved') NOT NULL DEFAULT 'Draft'"
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('ppmps') || !Schema::hasColumn('ppmps', 'status')) {
            return;
        }

        DB::statement("UPDATE `ppmps` SET `status` = 'Submitted' WHERE `status` = 'Draft'");

        DB::statement(
            "ALTER TABLE `ppmps` MODIFY `status` ENUM('Submitted','Approved','Disapproved') NOT NULL DEFAULT 'Submitted'"
        );
    }
};
