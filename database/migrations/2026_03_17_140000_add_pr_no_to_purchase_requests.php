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
            if (!Schema::hasColumn('purchase_requests', 'pr_no')) {
                $table->string('pr_no', 80)->nullable()->after('pr_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_requests')) {
            return;
        }

        Schema::table('purchase_requests', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requests', 'pr_no')) {
                $table->dropColumn('pr_no');
            }
        });
    }
};
