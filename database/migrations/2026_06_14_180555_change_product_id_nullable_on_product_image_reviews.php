<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_image_reviews', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id')
                ->nullable()
                ->change();

        });
    }

    public function down()
    {
        Schema::table('product_image_reviews', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id')
                ->nullable(false)
                ->change();

        });
    }
};
