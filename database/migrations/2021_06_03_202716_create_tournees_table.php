<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTourneesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournees', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->unsignedTinyInteger('nombre');
            $table->string('titre', 150)->unique();
            $table->unsignedBigInteger('produit');
            $table->foreign('produit')->references('id')->on('produits')->onDelete('cascade');
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
        Schema::dropIfExists('tournees');
    }
}
