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
        Schema::table('product_drafts', function (Blueprint $table) {

            $table->decimal('tax_rate', 5, 4)
                ->nullable();

            $table->boolean('is_taxable')
                ->default(false);

            $table->integer('display_price')
                ->nullable();

            $table->integer('display_shipping_fee')
                ->nullable();

            $table->integer('display_total_price')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_drafts', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
            $table->dropColumn('is_taxable');
            $table->dropColumn('display_price');
            $table->dropColumn('display_shipping_fee');
            $table->dropColumn('display_total_price');

        });
    }
};
