<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_requests')) {
            return;
        }

        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'bac_notice_type')) {
                $table->string('bac_notice_type', 20)->nullable()->after('failure_notice_path');
            }
            if (!Schema::hasColumn('purchase_requests', 'bac_notice_path')) {
                $table->string('bac_notice_path')->nullable()->after('bac_notice_type');
            }
        });

        // Backfill bac_notice_type and bac_notice_path from legacy columns
        if (Schema::hasColumn('purchase_requests', 'award_notice_path') && Schema::hasColumn('purchase_requests', 'failure_notice_path')) {
            $query = DB::table('purchase_requests')->where(function ($q) {
                $q->whereNotNull('award_notice_path')->where('award_notice_path', '!=', '')
                    ->orWhereNotNull('failure_notice_path')->where('failure_notice_path', '!=', '');
            });
            foreach ($query->get(['pr_id', 'award_notice_path', 'failure_notice_path']) as $row) {
                if (!empty($row->award_notice_path)) {
                    DB::table('purchase_requests')->where('pr_id', $row->pr_id)->update([
                        'bac_notice_type' => 'awarded',
                        'bac_notice_path' => $row->award_notice_path,
                    ]);
                } elseif (!empty($row->failure_notice_path)) {
                    DB::table('purchase_requests')->where('pr_id', $row->pr_id)->update([
                        'bac_notice_type' => 'failed',
                        'bac_notice_path' => $row->failure_notice_path,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_requests')) {
            return;
        }

        Schema::table('purchase_requests', function (Blueprint $table) {
            foreach (['bac_notice_type', 'bac_notice_path'] as $col) {
                if (Schema::hasColumn('purchase_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
