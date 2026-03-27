<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ppmps')) {
            return;
        }

        Schema::table('ppmps', function (Blueprint $table) {
            if (!Schema::hasColumn('ppmps', 'fiscal_year')) {
                $table->unsignedSmallInteger('fiscal_year')->nullable()->after('fund_source_id');
            }

            if (!Schema::hasColumn('ppmps', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('ppmps', 'review_remarks')) {
                $table->text('review_remarks')->nullable()->after('submitted_at');
            }

            if (!Schema::hasColumn('ppmps', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_remarks');
            }

            if (!Schema::hasColumn('ppmps', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ppmps')) {
            return;
        }

        Schema::table('ppmps', function (Blueprint $table) {
            foreach (['fiscal_year', 'submitted_at', 'review_remarks', 'reviewed_by', 'reviewed_at'] as $column) {
                if (Schema::hasColumn('ppmps', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
