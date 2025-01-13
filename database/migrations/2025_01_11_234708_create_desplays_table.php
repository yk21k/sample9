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
        Schema::create('desplays', function (Blueprint $table) {
            $table->id();
            $table->string('shop_id');
            $table->string('desplay_id');
            $table->string('shop_name')->nullable();
            $table->string("url")->nullable();
            $table->string("desplay_phone")->nullable();
            $table->string("desplay_area")->nullable();
            $table->string("desplay_real_store1")->nullable();
            $table->string("desplay_real_address1")->nullable();
            $table->string("desplay_real_store2")->nullable();
            $table->string("desplay_real_address2")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desplays');
    }
};
