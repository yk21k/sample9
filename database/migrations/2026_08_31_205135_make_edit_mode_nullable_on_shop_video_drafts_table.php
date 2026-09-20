<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {
            $table->string('edit_mode')
                ->nullable()
                ->default(null)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {
            $table->string('edit_mode')
                ->nullable(false)
                ->default('v1')
                ->change();
        });
    }
};