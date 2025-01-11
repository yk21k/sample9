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
        Schema::create('shop_profs', function (Blueprint $table) {
            $table->id();
            $table->string('shop_id');
            $table->string("pr1")->nullable();
            $table->string("pr2")->nullable();
            $table->string("pr3")->nullable();
            $table->string("physical_store1")->nullable();
            $table->string("physical_store2")->nullable();
            $table->string("physical_store3")->nullable();
            $table->string("zip_code1")->nullable();
            $table->string("zip_code2")->nullable();
            $table->string("zip_code3")->nullable();
            $table->string("address1")->nullable();
            $table->string("address2")->nullable();
            $table->string("address3")->nullable();
            $table->string("url")->nullable();
            $table->enum('free_delivery', ['yes', 'no'])->nullable();
            $table->string("delivery_memo")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_profs');
    }
};
