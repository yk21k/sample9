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
        Schema::table('auctions', function (Blueprint $table) {
            $table->unsignedBigInteger('winner_user_id')->nullable()->after('shop_id');
            $table->foreign('winner_user_id')->references('id')->on('users')->onDelete('set null');

            $table->float('final_price')->nullable()->after('spot_price');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->after('final_price');
            $table->dateTime('payment_at')->nullable()->after('payment_status');
            $table->string('payment_method')->nullable()->after('payment_at');
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeign(['winner_user_id']);
            $table->dropColumn(['winner_user_id', 'final_price', 'payment_status', 'payment_at', 'payment_method']);
        });
    }

};
