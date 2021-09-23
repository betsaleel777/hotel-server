<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEncaissementsReceptionsTableAddColumnDateSoldee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('encaissements_receptions', function (Blueprint $table) {
            $table->dateTime('date_soldee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('encaissements_receptions', function (Blueprint $table) {
            //
        });
    }
}
