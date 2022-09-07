<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAltFinancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alt_finances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('altName')->unique();
            $table->float('price',11,2);
            $table->string('catagory');
            $table->string('_option');
            $table->string('optionList');
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
        Schema::dropIfExists('alt_finances');
    }
}
