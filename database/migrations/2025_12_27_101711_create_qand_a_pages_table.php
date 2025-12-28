<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('q_and_a_pages', function (Blueprint $table) {
            $table->id();

            // 表示制御
            $table->string('slug')->unique(); // tax / shipping / commission
            $table->string('title');          // ページタイトル
            $table->string('question');       // 見出し用Q

            // 本文（詳細説明・HTML可）
            $table->longText('answer');

            // 表示順・公開制御
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('q_and_a_pages');
    }
};

