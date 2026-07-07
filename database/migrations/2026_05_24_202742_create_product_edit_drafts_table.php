<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'product_edit_drafts',
            function (Blueprint $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | relation
                |--------------------------------------------------------------------------
                */
                $table->foreignId('product_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('shop_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | basic
                |--------------------------------------------------------------------------
                */
                $table->string('name');

                $table->text('description')
                    ->nullable();

                $table->integer('price')
                    ->default(0);

                $table->integer('shipping_fee')
                    ->default(0);

                $table->integer('stock')
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | images
                |--------------------------------------------------------------------------
                */
                $table->string('cover_img')
                    ->nullable();

                $table->string('cover_img2')
                    ->nullable();

                $table->string('cover_img3')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | movie
                |--------------------------------------------------------------------------
                */
                $table->text('movie')
                    ->nullable();

                $table->string('movie_file')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | json
                |--------------------------------------------------------------------------
                */
                $table->json('product_attributes')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | review
                |--------------------------------------------------------------------------
                */
                $table->string('status')
                    ->default('owner_pending');

                /*
                |--------------------------------------------------------------------------
                | owner_pending
                | admin_pending
                | approved
                | rejected
                |--------------------------------------------------------------------------
                */

                $table->text('owner_comment')
                    ->nullable();

                $table->timestamp('approved_at')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'product_edit_drafts'
        );
    }
};
