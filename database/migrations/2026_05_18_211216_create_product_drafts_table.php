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
        Schema::create('product_drafts', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | 所属店舗
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('shop_id');

            /*
            |--------------------------------------------------------------------------
            | 作成者(manager/staff)
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('created_by');

            /*
            |--------------------------------------------------------------------------
            | 商品基本情報
            |--------------------------------------------------------------------------
            */
            $table->string('name');

            $table->text('description')->nullable();

            $table->integer('price')->default(0);

            $table->integer('stock')->default(0);

            /*
            |--------------------------------------------------------------------------
            | 画像
            |--------------------------------------------------------------------------
            */
            $table->string('cover_img')->nullable();

            /*
            |--------------------------------------------------------------------------
            | owner審査状態
            |--------------------------------------------------------------------------
            */
            $table->string('status')
                ->default('owner_pending');

            /*
            |--------------------------------------------------------------------------
            | 差し戻しコメント
            |--------------------------------------------------------------------------
            */
            $table->text('owner_comment')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | owner承認日時
            |--------------------------------------------------------------------------
            */
            $table->timestamp('approved_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | index
            |--------------------------------------------------------------------------
            */
            $table->index('shop_id');

            $table->index('created_by');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_drafts');
    }
};
