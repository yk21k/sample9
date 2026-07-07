<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {

            // 🔥 review_status が既にある場合は change()
            $table->string('review_status')
                ->default('draft')
                ->comment('draft / manager_pending / pending / approved / rejected')
                ->change();

            // 🔥 誰が承認したか（任意だが推奨）
            $table->unsignedBigInteger('approved_by')->nullable()->after('review_status');

            // 🔥 承認日時
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            // 🔥 index（重要）
            $table->index('review_status');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropColumn(['approved_by', 'approved_at']);

            $table->dropIndex(['review_status']);

            // 元に戻す（必要なら）
            $table->string('review_status')->default('draft')->change();
        });
    }
};
