<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToActivityLogsTable extends Migration
{
    public function up()
    {
        Schema::table('activity_logs', function (Blueprint $table) {

            // 🔍 user_id 検索用
            $table->index('user_id');

            // 🔍 日付検索用
            $table->index('created_at');

            // 🔍 対象検索用（複合）
            $table->index(['target_type', 'target_id'], 'activity_target_index');

        });
    }

    public function down()
    {
        Schema::table('activity_logs', function (Blueprint $table) {

            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex('activity_target_index');

        });
    }
}
