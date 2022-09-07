<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpd3sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opd3s', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hx')->nullable();
            $table->string('dx')->nullable();
            $table->text('result')->nullable();
            $table->string('status')->nullable()->default('Pending');
            $table->string('lab')->nullable();
            $table->string('lab_result')->nullable();
            $table->string('alt')->nullable();
            $table->string('alt_result')->nullable();
            $table->string('med')->nullable();
            $table->string('med_result')->nullable();
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
        Schema::dropIfExists('opd3s');
    }
}
