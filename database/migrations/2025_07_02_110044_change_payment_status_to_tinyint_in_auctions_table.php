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
        Schema::table('auctions', function (Blueprint $table) {
            $table->smallInteger('payment_status')->nullable()->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('auctions', function (Blueprint $table) {
            // enum に戻す（nullable を明示）
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->nullable(false)->change();
        });
    }
};
