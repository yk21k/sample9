<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_review_queues', function (Blueprint $table) {

            // 出品者ID
            $table->unsignedBigInteger('user_id')
                ->after('product_id')
                ->comment('審査申請した出品者');

            // 審査申請日時
            $table->timestamp('requested_at')
                ->nullable()
                ->after('user_id')
                ->comment('審査申請日時');


            // インデックス（超重要）
            $table->index(['user_id','requested_at']);
        });
    }

    public function down(): void
    {
        Schema::table('product_review_queues', function (Blueprint $table) {

            $table->dropIndex(['user_id','requested_at']);

            $table->dropColumn([
                'user_id',
                'requested_at',
            ]);
        });
    }
};
