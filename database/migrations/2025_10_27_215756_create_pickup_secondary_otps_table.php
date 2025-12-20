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
        Schema::create('pickup_secondary_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_otp_id')->constrained('pickup_otps')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('pickup_orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->enum('status', ['unused', 'used', 'expired'])->default('unused');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_secondary_otps');
    }
};
