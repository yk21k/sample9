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
        // pickup_order_items の新規作成（複数商品対応）
        Schema::create('pickup_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_order_id')->constrained('pickup_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->tinyInteger('type')->default(3); // 1=通常, 2=オークション, 3=店舗受取
            $table->date('pickup_date')->nullable();   // 商品単位の受取日
            $table->time('pickup_time')->nullable();   // 商品単位の受取時間
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // pickup_order_items 削除
        Schema::dropIfExists('pickup_order_items');
    }
};
