<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEtatApresAttributions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etat_apres_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fourniture_id')->constrained('fournitures')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('chambre_id')->constrained('chambres')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('attribution_id')->constrained('attributions')->onDelete('cascade')->onUpdate('cascade');
            $table->boolean('bon');
            $table->boolean('acceptable');
            $table->boolean('vetuste');
            $table->boolean('inexistant');
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
        Schema::dropIfExists('etat_apres_attributions');
    }
}
