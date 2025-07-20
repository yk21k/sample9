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

            $table->string('shipping_fullname')->after('end')->nullable();
            $table->string('shipping_address')->after('shipping_fullname')->nullable();
            $table->string('shipping_city')->after('shipping_address')->nullable();
            $table->string('shipping_state')->after('shipping_city')->nullable();
            $table->string('shipping_zipcode')->after('shipping_state')->nullable();
            $table->string('shipping_phone')->after('shipping_zipcode')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn('shipping_fullname');
            $table->dropColumn('shipping_address');
            $table->dropColumn('shipping_city');
            $table->dropColumn('shipping_state');
            $table->dropColumn('shipping_zipcode');
            $table->dropColumn('shipping_phone');
        });
    }
};
