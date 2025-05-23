<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_coupon_sub_order', function (Blueprint $table) {
            $table->id();

            // 外部キー：shop_coupon_id → shop_coupons.id
            $table->foreignId('shop_coupon_id')
                  ->constrained('shop_coupons')
                  ->onDelete('cascade');

            // 外部キー：sub_order_id → sub_orders.id
            $table->foreignId('sub_order_id')
                  ->constrained('sub_orders')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_coupon_sub_order');
    }
};

