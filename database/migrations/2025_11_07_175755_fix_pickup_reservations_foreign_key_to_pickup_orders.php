<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_reservations', function (Blueprint $table) {
            // ❌ まず既存の外部キーを削除（ordersを参照しているもの）
            $table->dropForeign(['order_id']);

            // ✅ pickup_orders.id を参照するように再設定
            $table->foreign('order_id')
                ->references('id')
                ->on('pickup_orders')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('pickup_reservations', function (Blueprint $table) {
            $table->dropForeign(['order_id']);

            // 元に戻す（ordersを参照）
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
        });
    }
};
