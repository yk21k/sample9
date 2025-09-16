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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate', 5, 2); // 税率 (例: 10.00, 8.00)
            $table->date('start_date')->nullable(); // 適用開始日
            $table->date('end_date')->nullable();   // 適用終了日（nullなら継続）
            $table->string('description')->nullable(); // 任意の説明（軽減税率など）
            $table->boolean('is_active')->default(true); // 有効フラグ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
