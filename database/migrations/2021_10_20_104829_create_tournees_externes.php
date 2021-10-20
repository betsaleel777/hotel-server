<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTourneesExternes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournees_externes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom')->unique();
            $table->unsignedTinyInteger('nombre');
            $table->unsignedInteger('prix_vente');
            $table->foreignId('article_id')->constrained('articles_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('restaurant_id')->constrained('restaurants_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('categorie_id')->nullable()->constrained('categories_tournees_externes')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('tournees_externes');
    }
}
