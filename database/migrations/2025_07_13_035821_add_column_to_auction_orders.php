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
            $table->tinyInteger('arrival_status')->nullable()->after('payment_at')->default(0);
            $table->string('arrival_message')->nullable()->after('arrival_status');
            $table->dateTime('arrival_confirmed_at')->nullable()->after('arrival_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auction_orders', function (Blueprint $table) {
            $table->dropColumn('arrival_status');
            $table->dropColumn('arrival_message');
            $table->dropColumn('arrival_confirmed_at');
        });
    }
};
