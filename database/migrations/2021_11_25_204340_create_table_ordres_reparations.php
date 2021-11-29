<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableOrdresReparations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordres_reparations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('entree');
            $table->dateTime('sortie');
            $table->longText('description')->nullable();
            $table->boolean('fermeture')->default(0);
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('reparation_id')->constrained('reparations')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('ordres_reparations');
    }
}
