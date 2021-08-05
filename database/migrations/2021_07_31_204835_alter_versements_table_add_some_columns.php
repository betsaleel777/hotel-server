<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterVersementsTableAddSomeColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->boolean('espece')->nullable();
            $table->unsignedInteger('monnaie')->nullable();
            $table->string('cheque', 50)->nullable();
            $table->unsignedBigInteger('mobile_money')->nullable();
            $table->foreign('mobile_money')->references('id')->on('mobile_money')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('versements', function (Blueprint $table) {
            //
        });
    }
}
