<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventories')) {
            return;
        }

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('item_name');
            $table->string('unit')->nullable();
            $table->integer('quantity_on_hand')->default(0);
            $table->unsignedBigInteger('last_delivery_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('inventories')) {
            Schema::drop('inventories');
        }
    }
};
