<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabFinancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_finances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('labName')->unique();
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
        Schema::dropIfExists('lab_finances');
    }
}
