<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableVersementsDepartements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('versements_departements', function (Blueprint $table) {
            $table->id();
            $table->unsignedinteger('montant');
            $table->unsignedBigInteger('encaissement');
            $table->boolean('espece')->nullable();
            $table->unsignedInteger('monnaie')->nullable();
            $table->string('cheque', 50)->nullable();
            $table->unsignedBigInteger('mobile_money')->nullable();
            $table->unsignedBigInteger('departement')->nullable();
            $table->foreign('mobile_money')->references('id')->on('mobile_money')->onDelete('cascade');
            $table->foreign('encaissement')->references('id')->on('encaissements')->onDelete('cascade');
            $table->foreign('departement')->references('id')->on('departements')->onDelete('cascade');
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
        Schema::dropIfExists('versements_departements');
    }
}
