<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrixTourneesExternes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prix_tournees_externes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('montant');
            $table->foreignId('restaurant_id')->constrained('restaurants_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('tournee_id')->constrained('tournees_externes')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('prix_tournees_externes');
    }
}
