<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (!Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number')->nullable();
            }

            if (!Schema::hasColumn('users', 'department_unit_id')) {
                $table->unsignedBigInteger('department_unit_id')->nullable();
            }

            if (!Schema::hasColumn('users', 'fund_source_id')) {
                $table->unsignedBigInteger('fund_source_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'contact_number')) {
                $table->dropColumn('contact_number');
            }

            if (Schema::hasColumn('users', 'department_unit_id')) {
                $table->dropColumn('department_unit_id');
            }

            if (Schema::hasColumn('users', 'fund_source_id')) {
                $table->dropColumn('fund_source_id');
            }
        });
    }
};