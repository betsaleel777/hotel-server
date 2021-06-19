<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrixCocktailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prix_cocktails', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('montant');
            $table->unsignedBigInteger('cocktail');
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
        Schema::dropIfExists('prix_cocktails');
    }
}
