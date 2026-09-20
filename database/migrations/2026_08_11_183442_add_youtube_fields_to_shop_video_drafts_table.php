<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {

            $table->enum('youtube_status', [
                'none',
                'uploading',
                'private',
                'public',
                'failed',
            ])
                ->default('none')
                ->after('preview_generated_at');

            $table->string('youtube_video_id')
                ->nullable()
                ->after('youtube_status');

            $table->timestamp('youtube_uploaded_at')
                ->nullable()
                ->after('youtube_video_id');
        });
    }

    public function down(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {
            $table->dropColumn([
                'youtube_status',
                'youtube_video_id',
                'youtube_uploaded_at',
            ]);
        });
    }
};