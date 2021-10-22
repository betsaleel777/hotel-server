<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMelangesExternesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('melanges_externes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cocktail_id')->constrained('cocktails_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('tournee_id')->constrained('tournees_externes')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('melanges_externes');
    }
}
