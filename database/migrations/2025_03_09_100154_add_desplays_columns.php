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
        Schema::table('desplays', function (Blueprint $table) {
            
            $table->string('desplay_mail')->after('desplay_phone')->nullable();

            $table->string('name1')->after('desplay_real_address2')->nullable();
            $table->string('photo1')->after('name1')->nullable();
            $table->string('description1')->after('photo1')->nullable();
            
            $table->string('name2')->after('description1')->nullable();
            $table->string('photo2')->after('name2')->nullable();
            $table->string('description2')->after('photo2')->nullable();
            
            $table->string('name3')->after('description2')->nullable();
            $table->string('photo3')->after('name3')->nullable();
            $table->string('description3')->after('photo3')->nullable();

            $table->boolean('is_active')->after('description3')->default(false);
            $table->string('desplay_pr')->after('is_active')->nullable();
            $table->string('seller_mail')->after('desplay_pr')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desplays', function (Blueprint $table) {

            $table->dropColumn('desplay_mail');

            $table->dropColumn('name1');
            $table->dropColumn('photo1');
            $table->dropColumn('description1');
            
            $table->dropColumn('name2');
            $table->dropColumn('photo2');
            $table->dropColumn('description2');
            
            $table->dropColumn('name3');
            $table->dropColumn('photo3');
            $table->dropColumn('description3');

            $table->dropColumn('is_active');
            $table->dropColumn('desplay_pr');
            $table->dropColumn('seller_mail');
            
        });
    }
};
