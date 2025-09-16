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
        Schema::table('sub_order_items', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('discounted_price');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('shipping_fee', 10, 2)->default(0)->after('tax_amount');
            $table->unsignedBigInteger('campaign_id')->nullable()->after('shipping_fee');
            $table->unsignedBigInteger('coupon_id')->nullable()->after('campaign_id');

            // 外部キー制約（任意）
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
            $table->foreign('coupon_id')->references('id')->on('shop_coupons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_order_items', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropColumn([
                'subtotal',
                'tax_amount',
                'shipping_fee',
                'campaign_id',
                'coupon_id'
            ]);
        });
    }
};
