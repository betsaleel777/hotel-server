<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTourneesEncaissementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournees_encaissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encaissement');
            $table->unsignedBigInteger('tournee');
            $table->unsignedDouble('quantite');
            $table->unsignedMediumInteger('prix_vente');
            $table->foreign('encaissement')->references('id')->on('encaissements')->onDelete('cascade');
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
        Schema::dropIfExists('tournees_encaissements');
    }
}
