<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesExternes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_externes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 200)->unique();
            $table->string('image', 255);
            $table->unsignedDecimal('contenance', 5, 3);
            $table->unsignedInteger('prix_vente');
            $table->string('mesure', 10);
            $table->string('etagere', 50);
            $table->boolean('pour_plat')->default(false);
            $table->boolean('pour_tournee')->default(false);
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
        Schema::dropIfExists('articles_externes');
    }
}
