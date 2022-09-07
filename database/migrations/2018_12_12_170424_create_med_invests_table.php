<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedInvestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('med_invests', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('medName')->nullable();
            $table->string('medResult')->nullable();
            $table->string('medStatus')->nullable()->default('Pending');
            $table->string('medDoz')->nullable();
            $table->longText('catagory');
            $table->string('duration');
            $table->string('frequency');
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
        Schema::dropIfExists('med_invests');
    }
}
