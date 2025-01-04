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
        Schema::create('extra_holiday', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name');
            $table->string('shop_id');
            $table->string("date_key", 8);
            $table->integer("date_flag")->default(0);
            $table->string("comment")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_holiday');
    }
};
