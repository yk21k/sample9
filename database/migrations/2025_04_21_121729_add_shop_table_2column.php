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
            $table->string('person_1')->after('license_expiry')->nullable(); 
            $table->string('person_2')->after('person_1')->nullable(); 
            $table->string('person_3')->after('person_2')->nullable(); 

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('person_1');
            $table->dropColumn('person_2');
            $table->dropColumn('person_3');

        });
    }
};
