<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_video_social_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_video_draft_id')
                ->constrained('product_video_drafts')
                ->cascadeOnDelete();

            $table->string('platform', 50);

            $table->string('status', 50)
                ->default('pending');

            $table->string('visibility', 50)
                ->nullable();

            $table->string('external_post_id')
                ->nullable();

            $table->timestamp('published_at')
                ->nullable();

            $table->text('error_message')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['product_video_draft_id', 'platform'],
                'pvs_draft_platform_unique'
            );

            $table->index([
                'platform',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_video_social_posts');
    }
};