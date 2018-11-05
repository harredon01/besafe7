<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeliveryManyMany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_stop', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_id')->unsigned()->nullable();
            $table->foreign('delivery_id')->references('id')
                    ->on('deliveries');
            $table->integer('stop_id')->unsigned()->nullable();
            $table->foreign('stop_id')->references('id')
                    ->on('stops');
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
        Schema::dropIfExists('delivery_stop');
    }
}
