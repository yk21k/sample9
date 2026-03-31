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
        Schema::table('product_review_queues', function (Blueprint $table) {
            $table->json('fix_fields')->nullable()->after('reviewer_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_review_queues', function (Blueprint $table) {
            $table->dropColumn('fix_fields');
        });
    }
};
