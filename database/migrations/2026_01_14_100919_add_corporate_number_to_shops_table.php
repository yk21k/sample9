<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table
                ->string('corporate_number', 13)
                ->after('license_expiry') // 位置は適宜変更
                ->nullable()
                ->comment('国税庁 法人番号（13桁）');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropUnique(['corporate_number']);
            $table->dropColumn('corporate_number');
        });
    }
};
