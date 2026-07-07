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
        Schema::table('product_drafts', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | AI画像集計
            |--------------------------------------------------------------------------
            */
            $table->unsignedTinyInteger('image_ai_total_count')
                ->default(0)
                ->after('image_ai_status');

            $table->unsignedTinyInteger('image_ai_ok_count')
                ->default(0)
                ->after('image_ai_total_count');

            $table->unsignedTinyInteger('image_ai_ng_count')
                ->default(0)
                ->after('image_ai_ok_count');

        });
    }

    public function down(): void
    {
        Schema::table('product_drafts', function (Blueprint $table) {

            $table->dropColumn([
                'image_ai_total_count',
                'image_ai_ok_count',
                'image_ai_ng_count',
            ]);

        });
    }
};
