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
        Schema::create('delete_shops', function (Blueprint $table) {
            $table->id();
            $table->text('reason');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shop_id')->nullable();

            $table->boolean('is_active')->default(false);
            $table->tinyInteger('status');

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delete_shops');
    }
};
