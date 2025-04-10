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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('status');
            $table->mediumText('description');
            $table->float('suggested_price');//希望
            $table->float('spot_price');//即決
            $table->float('shipping_fee');
            $table->dateTime('start');
            $table->dateTime('end');

            $table->string('cover_img1')->nullable();
            $table->string('cover_img2')->nullable();
            $table->string('cover_img3')->nullable();
            $table->string('cover_img4')->nullable();
            $table->string('cover_img5')->nullable();
            $table->string('cover_img6')->nullable();
            $table->string('cover_img7')->nullable();
            $table->string('movie')->nullable();

            $table->unsignedBigInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');

            $table->integer('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
