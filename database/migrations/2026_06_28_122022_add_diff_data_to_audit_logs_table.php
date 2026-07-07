<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            $table->json('diff_data')
                  ->nullable()
                  ->after('after_data');

        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            $table->dropColumn('diff_data');

        });
    }
};
