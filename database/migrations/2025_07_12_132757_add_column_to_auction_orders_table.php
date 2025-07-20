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
        Schema::table('auction_orders', function (Blueprint $table) {
            $table->unsignedInteger('shipping_fee')->nullable()->after('final_price');
            $table->dateTime('payment_at')->nullable()->after('winner_user_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auction_orders', function (Blueprint $table) {
            $table->dropColumn('shipping_fee');
            $table->dropColumn('payment_at');
        });
    }
};
