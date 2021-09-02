<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovisionementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvisionements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->unsignedInteger('prix_achat');
            $table->unsignedInteger('prix_vente')->nullable()->default(0);
            $table->unsignedMediumInteger('quantite');
            $table->unsignedBigInteger('ingredient');
            $table->foreign('ingredient')->references('id')->on('produits')->onDelete('cascade');
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
        Schema::dropIfExists('approvisionements');
    }
}
