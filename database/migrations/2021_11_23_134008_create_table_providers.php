<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProviders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('code', 20)->index()->unique();
            $table->string('telephone', 20)->unique();
            $table->string('prenom', 255);
            $table->string('email', 255)->nullable()->unique();
            $table->string('adresse')->nullable();
            $table->foreignId('categorie_id')->constrained('categories_reparations')->onDelete('cascade')->onUpdate('cascade');
            $table->softDeletes();
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
        Schema::dropIfExists('providers');
    }
}
