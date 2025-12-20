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
        Schema::table('pickup_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_orders', 'items')) {
                $table->dropColumn('items'); // JSONは削除
            }

            $table->string('status')->default('pending')->change(); // pending / confirmed / completed
            $table->string('payment_intent_id')->nullable(); // Stripe CheckoutのIDを保存（後で確認用）
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // pickup_orders の修正を元に戻す
        Schema::table('pickup_orders', function (Blueprint $table) {
            $table->json('items');

            $table->string('status')->default(null)->change();

            $table->dropColumn('payment_intent_id');
        });
    }
};
