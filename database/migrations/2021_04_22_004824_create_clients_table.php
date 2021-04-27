<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 90);
            $table->string('pere', 255);
            $table->string('mere', 255);
            $table->string('departement', 70);
            $table->string('pays', 60);
            $table->string('domicile', 200);
            $table->string('profession', 200);
            $table->string('prenom', 200);
            $table->string('email', 255)->unique();
            $table->string('contact', 20)->unique();
            $table->string('code', 12)->unique();
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
        Schema::dropIfExists('clients');
    }
}
