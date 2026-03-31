<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_review_queues', function (Blueprint $table) {

            $table->json('ai_result')->nullable()->after('status');

            $table->integer('ai_score')->default(0)->after('ai_result');

            $table->string('ai_status')->default('pending')->after('ai_score');
            // pending / done / error

            $table->timestamp('ai_checked_at')->nullable()->after('ai_status');

        });
    }

    public function down()
    {
        Schema::table('product_review_queues', function (Blueprint $table) {

            $table->dropColumn([
                'ai_result',
                'ai_score',
                'ai_status',
                'ai_checked_at'
            ]);

        });
    }
};
