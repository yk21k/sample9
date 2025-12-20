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
        Schema::create('pickup_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->string('name');
            $table->tinyInteger('status')->default(0);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('cover_img1')->nullable();
            $table->string('cover_img2')->nullable();
            $table->string('cover_img3')->nullable();
            $table->string('cover_img4')->nullable();
            $table->string('cover_img5')->nullable();
            $table->string('cover_img6')->nullable();
            $table->string('cover_img7')->nullable();
            $table->string('cover_img8')->nullable();
            $table->string('cover_img9')->nullable();
            $table->string('movie')->nullable();
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_products');
    }
};
