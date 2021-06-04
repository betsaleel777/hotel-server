<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrixTourneesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prix_tournees', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('montant');
            $table->unsignedBigInteger('tournee');
            $table->foreign('tournee')->references('id')->on('tournees')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prix_tournees');
    }
}
