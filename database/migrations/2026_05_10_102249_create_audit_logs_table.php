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
        Schema::create('audit_logs', function (Blueprint $table) {

            $table->id();

            // =========================
            // 🔥 実行ユーザー
            // =========================
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // =========================
            // 🔥 店舗
            // =========================
            $table->foreignId('shop_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // =========================
            // 🔥 操作
            // =========================
            $table->string('action');

            // =========================
            // 🔥 対象モデル
            // =========================
            $table->string('target_type')
                ->nullable();

            $table->unsignedBigInteger('target_id')
                ->nullable();

            // =========================
            // 🔥 変更前
            // =========================
            $table->json('before_data')
                ->nullable();

            // =========================
            // 🔥 変更後
            // =========================
            $table->json('after_data')
                ->nullable();

            // =========================
            // 🔥 メモ
            // =========================
            $table->text('description')
                ->nullable();

            // =========================
            // 🔥 IP
            // =========================
            $table->ipAddress('ip')
                ->nullable();

            // =========================
            // 🔥 user agent
            // =========================
            $table->text('user_agent')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
