<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEntretiens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entretiens', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->index()->unique();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('chambre_id')->constrained('chambres')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('entree');
            $table->dateTime('sortie');
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
        Schema::dropIfExists('entretiens');
    }
}
