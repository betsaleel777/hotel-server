<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFournituresReparations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fournitures_reparations', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('quantite');
            $table->string('equipement', 200)->nullable();
            $table->foreignId('fourniture_id')->constrained('fournitures')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('ordres_reparation_id')->constrained('ordres_reparations')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('fournitures_reparations');
    }
}
