<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 100)->change(); // 必要に応じて100以上でもOK
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->change(); // 元の長さに戻す（例）
        });
    }

};
