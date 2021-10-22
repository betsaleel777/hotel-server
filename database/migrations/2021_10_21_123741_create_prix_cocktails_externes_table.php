<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrixCocktailsExternesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prix_cocktails_externes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('montant');
            $table->foreignId('cocktail_id')->constrained('cocktails_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('restaurant_id')->constrained('restaurants_externes')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('prix_cocktails_externes');
    }
}
