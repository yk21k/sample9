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
            $table->string('intro_video_url')->nullable()->after('url');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_profs', function (Blueprint $table) {
            $table->dropColumn('intro_video_url');
        });
    }
};
