<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();

            // システム用キー（例: name, email, corporate_number）
            $table->string('name')->unique()->comment('システム識別キー');

            // 表示用
            $table->string('label')->comment('画面表示名');
            $table->text('purpose')->nullable()->comment('何に使うか');
            $table->text('recommendation')->nullable()->comment('推奨内容');

            // 検索用キーワード（JSON推奨）
            $table->json('search_keywords')->nullable()->comment('検索用キーワード');

            // 並び順
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
