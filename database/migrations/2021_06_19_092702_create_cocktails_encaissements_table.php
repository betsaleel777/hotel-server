<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCocktailsEncaissementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cocktails_encaissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encaissement');
            $table->unsignedBigInteger('cocktail');
            $table->unsignedDouble('quantite');
            $table->unsignedMediumInteger('prix_vente');
            $table->foreign('encaissement')->references('id')->on('encaissements')->onDelete('cascade');
            $table->foreign('cocktail')->references('id')->on('cocktails')->onDelete('cascade');

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
        Schema::dropIfExists('cocktails_encaissements');
    }
}
