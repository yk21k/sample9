<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_review_logs', function (Blueprint $table) {

            $table->id();

            // 商品
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // 審査した管理者
            $table->foreignId('reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 審査アクション
            $table->string('action');

            /*
            approve
            reject
            revision
            */

            // コメント
            $table->text('comment')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_review_logs');
    }
};

