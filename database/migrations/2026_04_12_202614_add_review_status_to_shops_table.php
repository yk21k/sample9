<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {

            // 審査ステータス
            $table->string('review_status')
                  ->default('approved')
                  ->after('status');

            // インデックス（一覧高速化）
            $table->index('review_status');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex(['review_status']);
            $table->dropColumn('review_status');
        });
    }
};
