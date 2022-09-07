<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAltInvestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alt_invests', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('altName')->nullable();
            $table->string('altResult')->nullable();
            $table->longText('catagory')->nullable();
            $table->string('altStatus')->nullable()->default('Pending');
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
        Schema::dropIfExists('alt_invests');
    }
}
