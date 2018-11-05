<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_id')->unsigned()->nullable();
            $table->string('starter_id')->nullable();
            $table->string('main_id')->nullable();
            $table->string('dessert_id')->nullable();
            $table->string('code')->nullable();
            $table->double('shipping')->nullable();
            $table->string('observation')->nullable();
            $table->integer('group_id')->unsigned()->nullable();
            $table->foreign('group_id')->references('id')
                    ->on('groups');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')
                    ->on('users');
            $table->integer('address_id')->unsigned();
	    $table->dateTime('delivery');
	    $table->text('details')->nullable();
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
        Schema::dropIfExists('deliveries');
    }
}
