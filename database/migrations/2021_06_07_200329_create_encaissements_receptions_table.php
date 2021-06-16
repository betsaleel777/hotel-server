<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEncaissementsReceptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('encaissements_receptions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('status', 50);
            $table->unsignedBigInteger('attribution')->nullable();
            $table->unsignedBigInteger('reservation')->nullable();
            $table->foreign('attribution')->references('id')->on('attributions')->onDelete('cascade');
            $table->foreign('reservation')->references('id')->on('reservations')->onDelete('cascade');
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
        Schema::dropIfExists('encaissements_receptions');
    }
}
