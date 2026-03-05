<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {

            $table->id();

            // 商品ID
            $table->unsignedBigInteger('product_id');

            // 審査した管理者
            $table->unsignedBigInteger('reviewer_id')->nullable();

            // 審査結果
            $table->tinyInteger('status');
            /*
            1 = 承認
            2 = 拒否
            3 = 保留
            */

            // コメント
            $table->text('comment')->nullable();

            // AI審査結果（将来用）
            $table->json('ai_result')->nullable();

            $table->timestamps();

            // index
            $table->index('product_id');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
