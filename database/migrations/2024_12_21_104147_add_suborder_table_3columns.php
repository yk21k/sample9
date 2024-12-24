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
        Schema::table('sub_orders', function (Blueprint $table) {
            $table->string('shipping_company')->nullable()->after('status');
            $table->string('invoice_number')->nullable()->after('shipping_company');
            $table->string('payment_status')->nullable()->after('invoice_number');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_orders', function (Blueprint $table) {
            $table->dropColumn('shipping_company');
            $table->dropColumn('invoice_number');
            $table->dropColumn('payment_status');
        });
    }
};
