<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatsEncaissementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plats_encaissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encaissement');
            $table->unsignedBigInteger('plat');
            $table->unsignedDouble('quantite');
            $table->unsignedMediumInteger('prix_vente');
            $table->foreign('encaissement')->references('id')->on('encaissements')->onDelete('cascade');
            $table->foreign('plat')->references('id')->on('plats')->onDelete('cascade');
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
        Schema::dropIfExists('plats_encaissements');
    }
}
