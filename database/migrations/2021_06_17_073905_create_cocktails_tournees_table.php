<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCocktailsTourneesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cocktails_tournees', function (Blueprint $table) {
            $table->id();
            $table->unsignedDouble('quantite');
            $table->unsignedBigInteger('tournee');
            $table->unsignedBigInteger('cocktail');
            $table->foreign('tournee')->references('id')->on('tournees')->onDelete('cascade');
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
        Schema::dropIfExists('cocktails_tournees');
    }
}
