<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            $table->string('role')->nullable();

            $table->unsignedBigInteger('product_id')
                ->nullable();

            $table->unsignedBigInteger('draft_id')
                ->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropColumn('product_id');
            $table->dropColumn('draft_id');
        });
    }
};
