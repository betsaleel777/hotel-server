<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaiementsExternesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paiements_externes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('montant');
            $table->unsignedInteger('monnaie')->default(0);
            $table->boolean('espece')->default(false);
            $table->string('cheque', 70)->nullable();
            $table->foreignId('mobile_id')->nullable()->constrained('mobiles_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('restaurant_id')->constrained('restaurants_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('facture_id')->constrained('factures_externes')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('paiements_externes');
    }
}
