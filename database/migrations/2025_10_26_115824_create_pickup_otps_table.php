<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pickup_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained('pickup_orders')->onDelete('cascade');
            $table->string('code', 6);
            $table->enum('status', ['unused', 'used', 'expired'])->default('unused');
            $table->timestamp('expires_at');
            $table->foreignId('used_by_shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_otps');
    }
};

