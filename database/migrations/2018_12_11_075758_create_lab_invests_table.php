<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabInvestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_invests', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('labName')->nullable();
            $table->string('labResult')->nullable();
            $table->longText('catagory')->nullable();
            $table->string('labStatus')->nullable()->default('Pending');
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
        Schema::dropIfExists('lab_invests');
    }
}
