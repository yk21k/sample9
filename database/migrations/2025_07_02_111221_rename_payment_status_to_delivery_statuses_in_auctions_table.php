<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePaymentStatusToDeliveryStatusesInAuctionsTable extends Migration
{
    public function up()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->renameColumn('payment_status', 'delivery_status');
        });
    }

    public function down()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->renameColumn('delivery_status', 'payment_status');
        });
    }
}

