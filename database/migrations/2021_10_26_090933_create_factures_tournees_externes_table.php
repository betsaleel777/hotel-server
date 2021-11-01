<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturesTourneesExternesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factures_tournees_externes', function (Blueprint $table) {
            $table->id();
            $table->unsignedDouble('quantite');
            $table->unsignedInteger('prix_vente');
            $table->foreignId('tournee_id')->constrained('tournees_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('facture_id')->constrained('factures_externes')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('factures_tournees_externes');
    }
}
