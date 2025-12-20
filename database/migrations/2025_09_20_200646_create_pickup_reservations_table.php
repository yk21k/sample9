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
        Schema::create('pickup_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_slot_id')->constrained();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('order_id')->nullable()->constrained(); // 決済後に紐付け
            $table->string('status')->default('reserved'); // reserved, cancelled, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_reservations');
    }
};
