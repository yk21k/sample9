<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_drafts', function (Blueprint $table) {

            $table->string('movie_file')
                ->nullable()
                ->after('movie');
        });
    }

    public function down(): void
    {
        Schema::table('product_drafts', function (Blueprint $table) {

            $table->dropColumn('movie_file');
        });
    }
};
