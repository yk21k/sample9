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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('photo_4')->nullable()->after('photo_3');
            $table->string('photo_5')->nullable()->after('photo_4');
            $table->string('photo_6')->nullable()->after('photo_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('photo_4');
            $table->dropColumn('photo_5');
            $table->dropColumn('photo_6');
        });
    }
};
