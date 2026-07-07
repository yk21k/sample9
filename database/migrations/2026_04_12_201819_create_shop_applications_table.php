<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_applications', function (Blueprint $table) {
            $table->id();

            // 出店者（users）
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // 既存ショップ（更新時のみ）
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();

            // new / update
            $table->string('type');

            // pending / approved / rejected
            $table->string('status')->default('pending');

            // JSON（変更前・変更後）
            $table->json('before_data')->nullable();
            $table->json('after_data');

            // 審査者
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            // 却下理由
            $table->text('reject_reason')->nullable();

            $table->timestamps();

            // 🔥 インデックス（重要）
            $table->index(['status']);
            $table->index(['user_id']);
            $table->index(['shop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_applications');
    }
};
