<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesEntretiensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes_entretiens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entretien_id')->constrained('entretiens')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedDecimal('valeur', 2, 1);
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
        Schema::dropIfExists('notes_entretiens');
    }
}
