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
        Schema::create('sub_orders_arrival_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_order_id')->constrained()->onDelete('cascade');
            $table->timestamp('confirmation_deadline')->nullable(); // ← 追加：回答期限
            $table->timestamp('confirmed_at')->nullable(); // ← 確認済みかどうか
            $table->boolean('arrival_reported')->default(false); // 到着報告の有無
            $table->text('comments')->nullable(); // コメント
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_orders_arrival_reports');
    }
};
