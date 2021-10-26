<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturesExternesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factures_externes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('status', 50);
            $table->foreignId('table_id')->nullable()->constrained('tables_externes')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('date_soldee')->nullable();
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
        Schema::dropIfExists('factures_externes');
    }
}
