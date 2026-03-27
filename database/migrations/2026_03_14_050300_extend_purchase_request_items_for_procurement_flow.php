<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_request_items')) {
            return;
        }

        Schema::table('purchase_request_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_request_items', 'ppmp_item_id')) {
                $table->unsignedBigInteger('ppmp_item_id')->nullable()->after('purchase_request_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_request_items')) {
            return;
        }

        Schema::table('purchase_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_request_items', 'ppmp_item_id')) {
                $table->dropColumn('ppmp_item_id');
            }
        });
    }
};
