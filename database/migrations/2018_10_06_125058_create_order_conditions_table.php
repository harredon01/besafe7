<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderConditionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type');
            $table->string('target');
            $table->string('value');
            $table->string('coupon')->nullable();
            $table->double('total');
            $table->integer('condition_id')->unsigned()->nullable();
            $table->integer('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')
                    ->on('orders');
            $table->integer('order')->unsigned()->default(0);
            $table->text('attributes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_conditions');
    }

}
