<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pickup_order_items', function (Blueprint $table) {
            $table->timestamp('pickup_mail_sent_at')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('pickup_order_items', function (Blueprint $table) {
            $table->dropColumn('pickup_mail_sent_at');
        });
    }

};
