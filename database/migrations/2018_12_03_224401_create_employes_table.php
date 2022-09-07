<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->increments('e_id');
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->text('about')->nullable();
            $table->string('addr')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->string('jop')->nullable();
            $table->string('status')->nullable();
            $table->string('e_status')->nullable();
            $table->string('role')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
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
        Schema::dropIfExists('employes');
    }
}
