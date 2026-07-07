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
        Schema::create('shop_members', function (Blueprint $table) {

            $table->id();

            // 🔥 どのショップか
            $table->unsignedBigInteger('shop_id');

            // 🔥 ログインユーザー（将来用）
            $table->unsignedBigInteger('user_id')->nullable();

            // 🔥 名前
            $table->string('name');

            // 🔥 本人確認ファイル
            $table->string('file1')->nullable();
            $table->string('file2')->nullable();

            // 🔥 権限
            $table->enum('role', ['owner', 'manager', 'staff'])
                ->default('staff');

            $table->timestamps();

            // 🔗 外部キー（推奨）
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_members');
    }
};
