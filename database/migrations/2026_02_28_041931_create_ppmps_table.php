<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('ppmps', function (Blueprint $table) {
        $table->id();

        // 🔥 THIS IS REQUIRED
        $table->foreignId('user_id')
              ->constrained()
              ->onDelete('cascade');

        $table->string('description')->nullable();
        $table->integer('quantity')->nullable();
        $table->decimal('estimated_cost', 12, 2)->nullable();

        $table->enum('quarter', ['Q1', 'Q2', 'Q3', 'Q4'])->nullable();

        $table->foreignId('fund_source_id')
              ->constrained('fund_sources')
              ->onDelete('cascade');

        $table->enum('status', [
            'Draft',
            'Submitted',
            'Approved',
            'Disapproved'
        ])->default('Draft');

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppmps');
    }
};
