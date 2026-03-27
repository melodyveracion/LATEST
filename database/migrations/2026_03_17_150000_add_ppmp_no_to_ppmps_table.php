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
            if (!Schema::hasColumn('ppmps', 'ppmp_no')) {
                $table->string('ppmp_no', 80)->nullable()->after('ppmp_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ppmps')) {
            return;
        }

        Schema::table('ppmps', function (Blueprint $table) {
            if (Schema::hasColumn('ppmps', 'ppmp_no')) {
                $table->dropColumn('ppmp_no');
            }
        });
    }
};
