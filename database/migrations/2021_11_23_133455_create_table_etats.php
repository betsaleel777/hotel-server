<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEtats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fourniture_id')->constrained('fournitures')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('chambre_id')->constrained('chambres')->onDelete('cascade')->onUpdate('cascade');
            $table->string('libelle', 25);
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
        Schema::dropIfExists('etats');
    }
}
