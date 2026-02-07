<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flows', function (Blueprint $table) {
            $table->id();

            // 識別用（ページ単位）
            $table->string('slug')->unique(); // 例: product_registration_flow
            $table->string('title');          // 表示タイトル
            $table->text('overview')->nullable(); // 大まかな流れ

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flows');
    }
};

