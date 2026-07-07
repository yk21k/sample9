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
        Schema::table('product_drafts', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | AI審査集約
            |--------------------------------------------------------------------------
            */

            $table->string('image_ai_status')
                ->default('pending')
                ->after('status');

            $table->string('video_ai_status')
                ->default('pending')
                ->after('image_ai_status');

            $table->string('text_ai_status')
                ->default('pending')
                ->after('video_ai_status');

            $table->string('overall_ai_status')
                ->default('pending')
                ->after('text_ai_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_drafts', function (Blueprint $table) {

            $table->dropColumn([

                'image_ai_status',

                'video_ai_status',

                'text_ai_status',

                'overall_ai_status',

            ]);

        });
    }
};
