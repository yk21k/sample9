<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_drafts', function (Blueprint $table) {

            $table->integer('shipping_fee')
                ->default(0)
                ->after('price');

            $table->string('cover_img2')
                ->nullable()
                ->after('cover_img');

            $table->string('cover_img3')
                ->nullable()
                ->after('cover_img2');

            $table->string('movie')
                ->nullable()
                ->after('cover_img3');

            /*
            |--------------------------------------------------------------------------
            | JSON attributes
            |--------------------------------------------------------------------------
            */
            $table->json('product_attributes')
                ->nullable()
                ->after('movie');
        });
    }

    public function down(): void
    {
        Schema::table('product_drafts', function (Blueprint $table) {

            $table->dropColumn([
                'shipping_fee',
                'cover_img2',
                'cover_img3',
                'movie',
                'product_attributes',
            ]);
        });
    }
};
