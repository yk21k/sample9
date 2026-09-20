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
        Schema::create('shop_video_bgms', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | BGM名称
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            /*
            |--------------------------------------------------------------------------
            | S3保存パス
            |--------------------------------------------------------------------------
            */

            $table->string('file_path');

            /*
            |--------------------------------------------------------------------------
            | BGMの長さ
            |--------------------------------------------------------------------------
            |
            | 秒
            |
            */

            $table->decimal(
                'duration',
                10,
                3
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | 利用可能か
            |--------------------------------------------------------------------------
            */

            $table->boolean(
                'is_active'
            )->default(true);

            /*
            |--------------------------------------------------------------------------
            | 表示順
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger(
                'sort_order'
            )->default(0);

            $table->timestamps();

            $table->index([
                'is_active',
                'sort_order',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'shop_video_bgms'
        );
    }
};