<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_orders', function (Blueprint $table) {
            // 存在する場合のみ削除（安全対応）
            if (Schema::hasColumn('pickup_orders', 'pickup_date')) {
                $table->dropColumn('pickup_date');
            }
            if (Schema::hasColumn('pickup_orders', 'pickup_time')) {
                $table->dropColumn('pickup_time');
            }

        });
    }

    public function down(): void
    {
        Schema::table('pickup_orders', function (Blueprint $table) {
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time')->nullable();
        });
    }
};
