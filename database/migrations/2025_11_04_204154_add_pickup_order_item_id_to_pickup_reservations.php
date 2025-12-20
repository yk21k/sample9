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
        Schema::table('pickup_reservations', function (Blueprint $table) {
            // pickup_order_item_id カラムを追加
            $table->foreignId('pickup_order_item_id')
                  ->nullable()
                  ->after('order_id')
                  ->constrained('pickup_order_items')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_reservations', function (Blueprint $table) {
            $table->dropForeign(['pickup_order_item_id']);
            $table->dropColumn('pickup_order_item_id');
        });
    }
};
