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
        Schema::table('pickup_order_items', function (Blueprint $table) {
            // pickup_slots テーブルの id と関連づける
            $table->foreignId('pickup_slot_id')->nullable()->after('pickup_time')->constrained('pickup_slots')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_order_items', function (Blueprint $table) {
            $table->dropForeign(['pickup_slot_id']);
            $table->dropColumn('pickup_slot_id');
        });
    }
};
