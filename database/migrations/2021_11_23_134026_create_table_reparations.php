<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableReparations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reparations', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('code', 20)->index()->unique();
            $table->foreignId('categorie_id')->constrained('categories_reparations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('chambre_id')->constrained('chambres')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('reparations');
    }
}
