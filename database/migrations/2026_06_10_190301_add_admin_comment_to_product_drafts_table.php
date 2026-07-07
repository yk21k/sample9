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
        Schema::table('product_drafts', function (Blueprint $table) {

            $table->text('admin_comment')
                ->nullable()
                ->after('owner_comment');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_drafts', function (Blueprint $table) {
            
            $table->dropColumn('admin_comment');

        });
    }
};
