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
        Schema::table(
            'shop_video_drafts',
            function (Blueprint $table) {

                $table->foreignId(
                    'bgm_id'
                )
                    ->nullable()
                    ->after('process_version')
                    ->constrained(
                        'shop_video_bgms'
                    )
                    ->nullOnDelete();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'shop_video_drafts',
            function (Blueprint $table) {

                $table->dropForeign([
                    'bgm_id',
                ]);

                $table->dropColumn(
                    'bgm_id'
                );
            }
        );
    }
};