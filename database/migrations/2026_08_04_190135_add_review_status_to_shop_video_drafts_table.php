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
            'shop_video_drafts',
            function (Blueprint $table) {

                $table->enum(
                    'review_status',
                    [
                        'pending',
                        'approved',
                        'rejected'
                    ]
                )
                ->default('pending')
                ->after('process_status');


                $table->timestamp(
                    'reviewed_at'
                )
                ->nullable();


                $table->unsignedBigInteger(
                    'reviewed_by'
                )
                ->nullable();

            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'shop_video_drafts',
            function (Blueprint $table) {

                $table->dropColumn([
                    'review_status',
                    'reviewed_at',
                    'reviewed_by',
                ]);

            }
        );
    }
};
