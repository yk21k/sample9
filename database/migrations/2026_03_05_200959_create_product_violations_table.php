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
        Schema::create('product_violations', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');

            $table->string('violation_type'); 
            // copyright / fake / adult / illegal / spam

            $table->text('reason')->nullable();

            $table->integer('severity')->default(1);
            // 1 軽微
            // 2 中
            // 3 重大

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_violations');
    }
};
