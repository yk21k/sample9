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
        Schema::create('product_video_drafts', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | 基本
            |--------------------------------------------------------------------------
            */

            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('shop_id')
                ->constrained('shops')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | 作成情報
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | 動画基本情報
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->text('description')
                ->nullable();

            $table->string('original_movie');

            $table->string('processed_movie')
                ->nullable();

            $table->string('thumbnail')
                ->nullable();

            $table->string('preview_movie')
                ->nullable();

            $table->integer('duration')
                ->nullable();

            $table->bigInteger('file_size')
                ->nullable();

            $table->string('mime_type')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | AI
            |--------------------------------------------------------------------------
            */

            $table->enum('ai_status', [
                'pending',
                'processing',
                'approved',
                'warning',
                'rejected',
                'failed',
            ])
                ->default('pending')
                ->index();

            $table->string('ai_provider')
                ->nullable();

            $table->string('rekognition_job_id')
                ->nullable();

            $table->integer('risk_score')
                ->nullable();

            $table->json('moderation_labels')
                ->nullable();

            $table->timestamp('ai_reviewed_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | 動画加工
            |--------------------------------------------------------------------------
            */

            $table->enum('process_status', [
                'waiting',
                'processing',
                'completed',
                'failed',
            ])
                ->default('waiting')
                ->index();

            $table->timestamp('processed_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Preview
            |--------------------------------------------------------------------------
            */

            $table->string('preview_status')
                ->default('waiting')
                ->index();

            $table->string('preview_url')
                ->nullable();

            $table->timestamp('preview_generated_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | 出品者確認
            |--------------------------------------------------------------------------
            */

            $table->enum('seller_review_status', [
                'pending',
                'approved',
            ])
                ->default('pending')
                ->index();

            $table->timestamp('seller_reviewed_at')
                ->nullable();

            $table->foreignId('seller_reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('seller_review_comment')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | 管理者審査
            |--------------------------------------------------------------------------
            */

            $table->enum('review_status', [
                'pending',
                'approved',
                'rejected',
            ])
                ->default('pending');

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('admin_comment')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | 動画編集
            |--------------------------------------------------------------------------
            */

            $table->string('edit_mode')
                ->default('pending');

            $table->foreignId('bgm_id')
                ->nullable()
                ->constrained('shop_video_bgms')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | YouTube
            |--------------------------------------------------------------------------
            */

            $table->enum('youtube_status', [
                'none',
                'uploading',
                'private',
                'public',
                'failed',
            ])
                ->default('none');

            $table->string('youtube_video_id')
                ->nullable();

            $table->timestamp('youtube_uploaded_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->string('workflow_stage')
                ->default('upload')
                ->index();


            /*
            |--------------------------------------------------------------------------
            | 有効状態
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(false);


            /*
            |--------------------------------------------------------------------------
            | Timestamp
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index([
                'product_id',
                'workflow_stage',
            ]);

            $table->index([
                'shop_id',
                'review_status',
            ]);

            $table->index([
                'youtube_status',
            ]);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_video_drafts');
    }
};