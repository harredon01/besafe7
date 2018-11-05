<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeliveryManyRoute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_route', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_id')->unsigned()->nullable();
            $table->foreign('delivery_id')->references('id')
                    ->on('deliveries');
            $table->integer('route_id')->unsigned()->nullable();
            $table->foreign('route_id')->references('id')
                    ->on('routes');
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
        Schema::dropIfExists('delivery_route');
    }
}
