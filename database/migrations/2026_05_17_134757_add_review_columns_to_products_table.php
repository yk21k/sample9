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
        Schema::table('products', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | 作成者(manager/staff)
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('submitted_by')
                ->nullable()
                ->after('shop_id');

            /*
            |--------------------------------------------------------------------------
            | owner 承認
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('owner_reviewed_by')
                ->nullable()
                ->after('submitted_by');

            $table->timestamp('owner_reviewed_at')
                ->nullable()
                ->after('owner_reviewed_by');

            /*
            |--------------------------------------------------------------------------
            | owner メモ
            |--------------------------------------------------------------------------
            */
            $table->text('owner_note')
                ->nullable()
                ->after('owner_reviewed_at');

            /*
            |--------------------------------------------------------------------------
            | admin メモ
            |--------------------------------------------------------------------------
            */
            $table->text('admin_note')
                ->nullable()
                ->after('owner_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropColumn([
                'submitted_by',
                'owner_reviewed_by',
                'owner_reviewed_at',
                'owner_note',
                'admin_note',
            ]);
        });
    }
};