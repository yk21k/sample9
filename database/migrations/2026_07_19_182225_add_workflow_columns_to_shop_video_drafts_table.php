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
        Schema::table('shop_video_drafts', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | 元動画情報
            |--------------------------------------------------------------------------
            */

            $table->string('mime_type')
                ->nullable()
                ->after('file_size');

            /*
            |--------------------------------------------------------------------------
            | AI
            |--------------------------------------------------------------------------
            */

            $table->string('ai_provider')
                ->nullable()
                ->after('ai_status');

            $table->integer('risk_score')
                ->nullable()
                ->after('ai_provider');

            $table->json('moderation_labels')
                ->nullable()
                ->after('risk_score');

            $table->timestamp('ai_reviewed_at')
                ->nullable()
                ->after('moderation_labels');

            /*
            |--------------------------------------------------------------------------
            | 加工
            |--------------------------------------------------------------------------
            */

            $table->string('process_version')
                ->default('v1')
                ->after('process_status');

            $table->timestamp('processed_at')
                ->nullable()
                ->after('process_version');

            /*
            |--------------------------------------------------------------------------
            | Preview
            |--------------------------------------------------------------------------
            */

            $table->string('preview_status')
                ->default('waiting')
                ->after('processed_at');

            $table->string('preview_url')
                ->nullable()
                ->after('preview_status');

            $table->timestamp('preview_generated_at')
                ->nullable()
                ->after('preview_url');

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->string('workflow_stage')
                ->default('upload')
                ->after('preview_generated_at');

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index('workflow_stage');
            $table->index('ai_status');
            $table->index('process_status');
            $table->index('preview_status');

            $table->index([
                'shop_id',
                'workflow_stage'
            ]);

            $table->index([
                'shop_id',
                'ai_status'
            ]);

        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {

            $table->dropIndex(['workflow_stage']);
            $table->dropIndex(['ai_status']);
            $table->dropIndex(['process_status']);
            $table->dropIndex(['preview_status']);

            $table->dropIndex([
                'shop_id',
                'workflow_stage'
            ]);

            $table->dropIndex([
                'shop_id',
                'ai_status'
            ]);

            $table->dropColumn([

                'mime_type',

                'ai_provider',

                'risk_score',

                'moderation_labels',

                'ai_reviewed_at',

                'process_version',

                'processed_at',

                'preview_status',

                'preview_url',

                'preview_generated_at',

                'workflow_stage',

            ]);

        });
    }
};
