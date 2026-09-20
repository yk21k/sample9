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
        Schema::table(
            'product_video_drafts',
            function (Blueprint $table) {
                $table
                    ->text('review_reject_reason')
                    ->nullable()
                    ->after('reviewed_by');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'product_video_drafts',
            function (Blueprint $table) {
                $table->dropColumn(
                    'review_reject_reason'
                );
            }
        );
    }
};