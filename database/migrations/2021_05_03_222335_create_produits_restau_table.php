<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProduitsRestauTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produits_restau', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->unsignedTinyInteger('seuil');
            $table->string('image', 255)->nullable();
            $table->string('mode', 20);
            $table->string('type', 50);
            $table->string('code', 20)->unique();
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
        Schema::dropIfExists('produits_restau');
    }
}
