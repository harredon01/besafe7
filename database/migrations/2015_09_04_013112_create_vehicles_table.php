<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vehicles', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->integer('category_id')->unsigned();
                        $table->foreign('category_id')->references('id')
                            ->on('categories');
                        $table->integer('user_id')->unsigned()->nullable();
                        $table->foreign('user_id')->references('id')
                            ->on('users');
                        $table->integer('axis');
                        $table->string('plates');
                        $table->string('image');
                        $table->string('make');
                        $table->string('model');
                        $table->string('color');
                        $table->string('vin_number');
                        $table->integer('year');
                        $table->double('full_length');
                        $table->string('horse_power');
                        $table->double('cargo_width');
                        $table->double('cargo_length');
                        $table->double('cargo_height');
                        $table->double('cargo_weight');
                        $table->string('description');
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
		Schema::drop('vehicles');
	}

}
