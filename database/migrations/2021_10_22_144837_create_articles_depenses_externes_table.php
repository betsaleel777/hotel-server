<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesDepensesExternesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_depenses_externes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depense_id')->constrained('depenses_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('article_id')->constrained('articles_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('cout');
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
        Schema::dropIfExists('articles_depenses_externes');
    }
}
