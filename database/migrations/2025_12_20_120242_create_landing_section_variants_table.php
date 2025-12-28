<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_section_variants', function (Blueprint $table) {
            $table->id();

            // 誰向けのセクションか
            $table->string('section_type', 20); // seller / buyer

            // バリエーション識別子（A / B / campaign_xxx）
            $table->string('variant_key', 50);

            // 表示用
            $table->string('title', 100);
            $table->text('description')->nullable();

            // CTA
            $table->string('cta_text', 50)->nullable();
            $table->string('cta_url', 255)->nullable();

            // 制御系
            $table->boolean('is_active')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // 検索・制約用
            $table->index(['section_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_section_variants');
    }
};

