<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngredientsExternesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredients_externes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plat_id')->constrained('plats_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('article_id')->constrained('articles_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedDouble('quantite');
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
        Schema::dropIfExists('ingredients_externes');
    }
}
