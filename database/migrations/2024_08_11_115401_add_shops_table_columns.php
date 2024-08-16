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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('location_1')->nullable()->after('rating');
            $table->string('location_2')->nullable()->after('location_1');
            $table->integer('telephone')->nullable()->after('location_2');
            $table->string('email')->nullable()->after('telephone');
            $table->string('identification_1')->nullable()->after('email');
            $table->string('identification_2')->nullable()->after('identification_1');
            $table->string('identification_3')->nullable()->after('identification_2');
            $table->string('photo_1')->nullable()->after('identification_3');
            $table->string('photo_2')->nullable()->after('photo_1');
            $table->string('photo_3')->nullable()->after('photo_2');
            $table->string('file_1')->nullable()->after('photo_3');
            $table->string('file_2')->nullable()->after('file_1');
            $table->string('file_3')->nullable()->after('file_2');
            $table->string('file_4')->nullable()->after('file_3');
            $table->string('representative')->nullable()->after('file_4');
            $table->string('manager')->nullable()->after('representative');
            $table->string('product_type')->nullable()->after('manager');
            $table->string('volume')->nullable()->after('product_type');
            $table->string('note')->nullable()->after('volume');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('location_1');
            $table->dropColumn('location_2');
            $table->dropColumn('telephone');
            $table->dropColumn('email');
            $table->dropColumn('identification_1');
            $table->dropColumn('identification_2');
            $table->dropColumn('identification_3');
            $table->dropColumn('photo_1');
            $table->dropColumn('photo_2');
            $table->dropColumn('photo_3');
            $table->dropColumn('file_1');
            $table->dropColumn('file_2');
            $table->dropColumn('file_3');
            $table->dropColumn('file_4');
            $table->dropColumn('representative');
            $table->dropColumn('manager');
            $table->dropColumn('product_type');
            $table->dropColumn('volume');
            $table->dropColumn('note');
        });
    }
};
