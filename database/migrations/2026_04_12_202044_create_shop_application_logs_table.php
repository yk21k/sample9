<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_application_logs', function (Blueprint $table) {
            $table->id();

            // 対象申請
            $table->foreignId('shop_application_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // 操作したユーザー（審査者）
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // approved / rejected / commented など
            $table->string('action');

            // コメント
            $table->text('comment')->nullable();

            $table->timestamps();

            // 🔥 インデックス
            $table->index(['shop_application_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_application_logs');
    }
};
