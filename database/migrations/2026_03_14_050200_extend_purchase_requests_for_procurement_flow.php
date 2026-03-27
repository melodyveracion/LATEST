<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_requests')) {
            return;
        }

        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'ppmp_id')) {
                $table->unsignedBigInteger('ppmp_id')->nullable()->after('fund_source_id');
            }

            if (!Schema::hasColumn('purchase_requests', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('purchase_requests', 'review_remarks')) {
                $table->text('review_remarks')->nullable()->after('submitted_at');
            }

            if (!Schema::hasColumn('purchase_requests', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_remarks');
            }

            if (!Schema::hasColumn('purchase_requests', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_requests')) {
            return;
        }

        Schema::table('purchase_requests', function (Blueprint $table) {
            foreach (['ppmp_id', 'submitted_at', 'review_remarks', 'reviewed_by', 'reviewed_at'] as $column) {
                if (Schema::hasColumn('purchase_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
