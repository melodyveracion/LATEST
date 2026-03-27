<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ppmp_items')) {
            return;
        }

        Schema::table('ppmp_items', function (Blueprint $table) {
            if (!Schema::hasColumn('ppmp_items', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('ppmp_id');
            }

            if (!Schema::hasColumn('ppmp_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('category_id');
            }

            if (!Schema::hasColumn('ppmp_items', 'specifications')) {
                $table->text('specifications')->nullable()->after('item_name');
            }

            if (!Schema::hasColumn('ppmp_items', 'quantity_q1')) {
                $table->integer('quantity_q1')->default(0)->after('estimated_budget');
            }

            if (!Schema::hasColumn('ppmp_items', 'quantity_q2')) {
                $table->integer('quantity_q2')->default(0)->after('quantity_q1');
            }

            if (!Schema::hasColumn('ppmp_items', 'quantity_q3')) {
                $table->integer('quantity_q3')->default(0)->after('quantity_q2');
            }

            if (!Schema::hasColumn('ppmp_items', 'quantity_q4')) {
                $table->integer('quantity_q4')->default(0)->after('quantity_q3');
            }

            if (!Schema::hasColumn('ppmp_items', 'q1_total_cost')) {
                $table->decimal('q1_total_cost', 15, 2)->default(0)->after('quantity_q4');
            }

            if (!Schema::hasColumn('ppmp_items', 'q2_total_cost')) {
                $table->decimal('q2_total_cost', 15, 2)->default(0)->after('q1_total_cost');
            }

            if (!Schema::hasColumn('ppmp_items', 'q3_total_cost')) {
                $table->decimal('q3_total_cost', 15, 2)->default(0)->after('q2_total_cost');
            }

            if (!Schema::hasColumn('ppmp_items', 'q4_total_cost')) {
                $table->decimal('q4_total_cost', 15, 2)->default(0)->after('q3_total_cost');
            }
        });

        DB::statement("UPDATE ppmp_items SET item_name = description WHERE item_name IS NULL OR item_name = ''");
        DB::statement("UPDATE ppmp_items SET quantity_q1 = quantity WHERE quantity_q1 = 0 AND quantity > 0");
        DB::statement("UPDATE ppmp_items SET q1_total_cost = estimated_budget WHERE q1_total_cost = 0 AND estimated_budget > 0");

        if (Schema::hasTable('preset_items')) {
            DB::statement("
                UPDATE ppmp_items
                INNER JOIN preset_items ON preset_items.item_name = ppmp_items.item_name
                SET ppmp_items.category_id = preset_items.category_id
                WHERE ppmp_items.category_id IS NULL
            ");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('ppmp_items')) {
            return;
        }

        Schema::table('ppmp_items', function (Blueprint $table) {
            foreach ([
                'category_id',
                'item_name',
                'specifications',
                'quantity_q1',
                'quantity_q2',
                'quantity_q3',
                'quantity_q4',
                'q1_total_cost',
                'q2_total_cost',
                'q3_total_cost',
                'q4_total_cost',
            ] as $column) {
                if (Schema::hasColumn('ppmp_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
