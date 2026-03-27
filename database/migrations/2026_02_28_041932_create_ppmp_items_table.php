<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ppmp_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ppmp_id')
          ->constrained('ppmps')
          ->onDelete('cascade');

    $table->string('uacs_code')->nullable();
    $table->text('description');
    $table->integer('quantity');
    $table->string('unit')->nullable();
    $table->decimal('unit_cost', 12, 2);
    $table->decimal('estimated_budget', 15, 2);
    $table->string('mode_of_procurement')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppmp_items');
    }
};
