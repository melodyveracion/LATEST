<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('consolidated_items')) {
            Schema::create('consolidated_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('item_name');
                $table->text('specifications')->nullable();
                $table->string('unit')->nullable();
                $table->integer('total_quantity')->default(0);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('estimated_budget', 15, 2)->default(0);
                $table->string('status')->default('Draft');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('consolidated_item_sources')) {
            Schema::create('consolidated_item_sources', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('consolidated_item_id');
                $table->unsignedBigInteger('purchase_request_id')->nullable();
                $table->unsignedBigInteger('purchase_request_item_id')->nullable();
                $table->integer('source_quantity')->default(0);
                $table->decimal('source_amount', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('biddings')) {
            Schema::create('biddings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('consolidated_item_id')->nullable();
                $table->string('supplier_name');
                $table->decimal('bid_amount', 15, 2)->default(0);
                $table->string('status')->default('Pending');
                $table->text('remarks')->nullable();
                $table->timestamp('bid_submitted_at')->nullable();
                $table->timestamp('awarded_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_request_id')->nullable();
                $table->unsignedBigInteger('consolidated_item_id')->nullable();
                $table->string('supplier_name')->nullable();
                $table->date('delivery_date')->nullable();
                $table->string('received_by')->nullable();
                $table->integer('quantity_delivered')->default(0);
                $table->string('status')->default('Pending');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['deliveries', 'biddings', 'consolidated_item_sources', 'consolidated_items'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }
};
