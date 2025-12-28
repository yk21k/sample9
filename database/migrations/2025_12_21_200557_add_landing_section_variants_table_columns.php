<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_section_variants', function (Blueprint $table) {
            // JSON でリスト項目を保存
            $table->json('features')->nullable()->after('description');

            // 背景色・ボタン色
            $table->string('bg_color', 20)->nullable()->after('cta_url');
            $table->string('btn_color', 20)->nullable()->after('bg_color');
        });
    }

    public function down(): void
    {
        Schema::table('landing_section_variants', function (Blueprint $table) {
            $table->dropColumn(['features', 'bg_color', 'btn_color']);
        });
    }
};

