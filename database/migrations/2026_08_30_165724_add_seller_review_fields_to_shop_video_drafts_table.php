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

            $table->enum(
                'seller_review_status',
                [
                    'pending',
                    'approved',
                ]
            )
            ->default('pending')
            ->after('process_status');

            $table->timestamp(
                'seller_reviewed_at'
            )
            ->nullable()
            ->after('seller_review_status');

            $table->unsignedBigInteger(
                'seller_reviewed_by'
            )
            ->nullable()
            ->after('seller_reviewed_at');

            $table->text(
                'seller_review_comment'
            )
            ->nullable()
            ->after('seller_reviewed_by');

            $table->index(
                'seller_review_status'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {

            $table->dropIndex([
                'seller_review_status'
            ]);

            $table->dropColumn([
                'seller_review_status',
                'seller_reviewed_at',
                'seller_reviewed_by',
                'seller_review_comment',
            ]);
        });
    }
};