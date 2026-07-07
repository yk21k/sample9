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
        Schema::table('activity_logs', function (Blueprint $table) {

            $table->unsignedBigInteger('shop_id')
                ->nullable()
                ->after('user_id');

            $table->unsignedBigInteger('product_id')
                ->nullable()
                ->after('shop_id');

            $table->unsignedBigInteger('draft_id')
                ->nullable()
                ->after('product_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn('shop_id');
            $table->dropColumn('product_id');
            $table->dropColumn('draft_id');

        });
    }
};
