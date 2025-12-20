<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('daily_qr_codes', function (Blueprint $table) {
            $table->dropColumn('signed_url');
            $table->dropColumn('expires_at');
        });
    }

    public function down()
    {
        Schema::table('daily_qr_codes', function (Blueprint $table) {
            $table->string('signed_url')->nullable();
            $table->dateTime('expires_at')->nullable();
        });
    }
};

