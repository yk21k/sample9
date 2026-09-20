<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE shop_video_drafts
            MODIFY ai_status ENUM(
                'pending',
                'processing',
                'approved',
                'warning',
                'rejected',
                'failed'
            )
            DEFAULT 'pending'
        ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE shop_video_drafts
            MODIFY ai_status ENUM(
                'pending',
                'approved',
                'warning',
                'rejected'
            )
            DEFAULT 'pending'
        ");
    }
};