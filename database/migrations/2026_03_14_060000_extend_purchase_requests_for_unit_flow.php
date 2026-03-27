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
            if (!Schema::hasColumn('purchase_requests', 'purpose')) {
                $table->text('purpose')->nullable()->after('ppmp_id');
            }

            if (!Schema::hasColumn('purchase_requests', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('submitted_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_requests')) {
            return;
        }

        Schema::table('purchase_requests', function (Blueprint $table) {
            foreach (['purpose', 'confirmed_at'] as $column) {
                if (Schema::hasColumn('purchase_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
