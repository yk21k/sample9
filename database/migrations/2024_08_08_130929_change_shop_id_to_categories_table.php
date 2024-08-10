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
        Schema::table('categories', function (Blueprint $table) {
            DB::statement("ALTER TABLE categories MODIFY COLUMN shop_id BIGINT NOT NULL AFTER name");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            DB::statement("ALTER TABLE categories MODIFY COLUMN shop_id BIGINT NOT NULL AFTER slug");
        });
    }
};
