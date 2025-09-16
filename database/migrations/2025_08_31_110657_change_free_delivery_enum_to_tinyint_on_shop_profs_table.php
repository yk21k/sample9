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
        Schema::table('shop_profs', function (Blueprint $table) {
            $table->smallInteger('free_delivery')->nullable()->default(0)->change();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_profs', function (Blueprint $table) {
            $table->enum('free_delivery', ['yes', 'no'])->nullable()->change();

        });
    }
};
