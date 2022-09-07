<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fullName')->nullable();
            $table->string('type')->nullable();
            $table->string('phone')->nullable();
            $table->string('age')->nullable();
            $table->string('reagion')->nullable();
            $table->string('hous_num')->nullable();
            $table->string('subcity')->nullable();
            $table->string('gender')->nullable();
            $table->string('status')->nullable();
            $table->string('opd_num')->nullable();
            $table->string('dateBefore')->nullable();


            $table->float('total_fee',9,2)->nullable();
            $table->float('discount_fee',9,2)->nullable();
            $table->float('net_fee',9,2)->nullable();
            $table->float('lab_total_fee',9,2)->nullable();
            $table->float('lab_discount_fee',9,2)->nullable();
            $table->float('lab_net_fee',9,2)->nullable();
            $table->float('alt_total_fee',9,2)->nullable();
            $table->float('alt_discount_fee',9,2)->nullable();
            $table->float('alt_net_fee',9,2)->nullable();
            $table->float('med_total_fee',9,2)->nullable();
            $table->float('med_discount_fee',9,2)->nullable();
            $table->float('med_net_fee',9,2)->nullable();
            
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
        Schema::dropIfExists('receptions');
    }
}
