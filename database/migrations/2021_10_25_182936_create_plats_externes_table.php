<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatsExternesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plats_externes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom')->unique();
            $table->unsignedInteger('prix_vente');
            $table->longText('description')->nullable();
            $table->foreignId('restaurant_id')->constrained('restaurants_externes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('categorie_id')->constrained('categories_plats_externes')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('plats_externes');
    }
}
