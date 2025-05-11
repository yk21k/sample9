<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCategoryIdToCategoriesIdInCategoryAuctionTable extends Migration
{
    public function up(): void
    {
        Schema::table('category_auction', function (Blueprint $table) {
            // まず外部キー制約を削除
            $table->dropForeign(['category_id']);

            // カラム名を変更
            $table->renameColumn('category_id', 'categories_id');

            // 新しい外部キー制約を再追加
            $table->foreign('categories_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('category_auction', function (Blueprint $table) {
            $table->dropForeign(['categories_id']);
            $table->renameColumn('categories_id', 'category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }
}

