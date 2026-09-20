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
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {
            $table->string('edit_mode')
                ->default('v1')
                ->change();
        });
    }
};