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
        Schema::table('desplays', function (Blueprint $table) {
            $table->string('display_shop_name')->after('shop_name')->nullable();
            $table->string('desplay_real_store3')->after('desplay_real_address2')->nullable();
            $table->string("desplay_real_address3")->after('desplay_real_store3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desplays', function (Blueprint $table) {
            $table->dropColumn('display_shop_name');
            $table->dropColumn('desplay_real_store3');
            $table->dropColumn('desplay_real_address3');
        });
    }
};
