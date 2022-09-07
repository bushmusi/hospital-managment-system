<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashiers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lastSeen');
            $table->string('dateBefore');
            $table->float('total_fee',9,2)->nullable();
            $table->float('discount_fee',9,2)->nullable();
            $table->float('net_fee',9,2)->nullable();
            $table->string('status')->nullable();
            $table->float('lab_total_fee',9,2)->nullable();
            $table->float('lab_discount_fee',9,2)->nullable();
            $table->float('lab_net_fee',9,2)->nullable();
            $table->string('lab_status')->nullable()->default('Unpaid');
            $table->float('alt_total_fee',9,2)->nullable();
            $table->float('alt_discount_fee',9,2)->nullable();
            $table->float('alt_net_fee',9,2)->nullable();
            $table->string('alt_status')->nullable()->default('Unpaid');
            $table->float('med_total_fee',9,2)->nullable();
            $table->float('med_discount_fee',9,2)->nullable();
            $table->float('med_net_fee',9,2)->nullable();
            $table->string('med_status')->nullable()->default('Unpaid');
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
        Schema::dropIfExists('cashiers');
    }
}
