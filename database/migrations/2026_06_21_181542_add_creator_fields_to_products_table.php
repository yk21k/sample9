<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->foreignId('original_created_by')
                ->nullable()
                ->after('shop_id');

            $table->foreignId('last_submitted_by')
                ->nullable()
                ->after('original_created_by');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropColumn([
                'original_created_by',
                'last_submitted_by',
            ]);
        });
    }
};
