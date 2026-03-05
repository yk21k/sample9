<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // 審査コメント
            $table->text('review_comment')->nullable()->after('status');

            // 審査した管理者ID
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_comment');

            // 審査日時
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropColumn([
                'review_comment',
                'reviewed_by',
                'reviewed_at'
            ]);

        });
    }
};
