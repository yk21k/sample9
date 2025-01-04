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
        Schema::create('holiday_setting', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name');
            $table->string('shop_id');
            $table->integer("flag_mon")->nullable();
            $table->integer("flag_tue")->nullable();
            $table->integer("flag_wed")->nullable();
            $table->integer("flag_thu")->nullable();
            $table->integer("flag_fri")->nullable();
            $table->integer("flag_sat")->nullable();
            $table->integer("flag_sun")->nullable();
            $table->integer("flag_holiday")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_setting');
    }
};
