<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiecesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pieces', function (Blueprint $table) {
            $table->id();
            $table->string('nature');
            $table->string('numero_piece');
            $table->date('delivre_le')->nullable();
            $table->date('expire_le')->nullable();
            $table->string('lieu_piece')->nullable();
            $table->string('maker')->nullable();
            $table->date('entree_pays')->nullable();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients')->onDelete('cascade');
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
        Schema::dropIfExists('pieces');
    }
}
