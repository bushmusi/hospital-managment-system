<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->increments('p_id');
            $table->string('date')->nullable();
            $table->string('history')->nullable();
            $table->string('dx')->nullable();
            $table->string('labName')->nullable();
            $table->string('labResult')->nullable();
            $table->string('altName')->nullable();
            $table->string('altResult')->nullable();
            $table->string('result')->nullable();
            $table->string('medName')->nullable();
            $table->string('medDoz')->nullable();
            $table->string('treatedBy')->nullable();
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
        Schema::dropIfExists('histories');
    }
}
