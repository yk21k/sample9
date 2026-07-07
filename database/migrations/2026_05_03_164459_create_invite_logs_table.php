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
        Schema::create('invite_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_member_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('token')->nullable();
            $table->string('status'); // sent / resent / expired / registered
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invite_logs');
    }
};
