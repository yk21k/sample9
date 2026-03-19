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
        Schema::create('product_review_queues', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->enum('status',[
                'pending',
                'reviewing',
                'approved',
                'rejected',
                'fix_requested'
            ])->default('pending');

            // 審査担当
            $table->foreignId('reviewer_id')->nullable()->constrained('users');

            // AI危険度
            $table->integer('risk_score')->default(0);

            // 審査コメント
            $table->text('comment')->nullable();

            // 審査開始
            $table->timestamp('review_started_at')->nullable();

            // 審査終了
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review_queues');
    }
};
