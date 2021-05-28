<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProduitsDemandesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produits_demandes', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('quantite');
            $table->unsignedBigInteger('produit');
            $table->unsignedBigInteger('demande');
            $table->foreign('produit')->references('id')->on('produits')->onDelete('cascade');
            $table->foreign('demande')->references('id')->on('demandes')->onDelete('cascade');
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
        Schema::dropIfExists('produits_demandes');
    }
}
