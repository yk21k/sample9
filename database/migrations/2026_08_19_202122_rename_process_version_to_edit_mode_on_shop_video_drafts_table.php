<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {
            $table->renameColumn(
                'process_version',
                'edit_mode'
            );
        });
    }

    public function down(): void
    {
        Schema::table('shop_video_drafts', function (Blueprint $table) {
            $table->renameColumn(
                'edit_mode',
                'process_version'
            );
        });
    }
};