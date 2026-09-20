<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_video_usage_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('shop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('video_id')
                ->nullable()
                ->constrained('shop_video_drafts')
                ->nullOnDelete();

            $table->string('usage_type');

            $table->date('usage_date');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | 同じ動画の二重消費防止
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'video_id',
                'usage_type',
            ]);

            /*
            |--------------------------------------------------------------------------
            | 日別集計用
            |--------------------------------------------------------------------------
            */

            $table->index([
                'shop_id',
                'usage_date',
                'usage_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'shop_video_usage_logs'
        );
    }
};