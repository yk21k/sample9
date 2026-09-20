<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_video_reviews', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | 対象動画
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'shop_video_draft_id'
            )
            ->constrained(
                'shop_video_drafts'
            )
            ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | 状態
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'status',
                [
                    'pending',
                    'approved',
                    'rejected'
                ]
            )
            ->default('pending');


            /*
            |--------------------------------------------------------------------------
            | 確認者
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'reviewer_id'
            )
            ->nullable()
            ->constrained(
                'users'
            )
            ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | コメント
            |--------------------------------------------------------------------------
            */

            $table->text(
                'comment'
            )
            ->nullable();


            $table->timestamp(
                'reviewed_at'
            )
            ->nullable();


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'shop_video_reviews'
        );
    }
};