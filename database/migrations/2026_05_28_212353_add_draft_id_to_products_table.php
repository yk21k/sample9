<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->foreignId('draft_id')
                ->nullable()
                ->after('shop_id')
                ->constrained('product_drafts')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropForeign(['draft_id']);
            $table->dropColumn('draft_id');

        });
    }
};
