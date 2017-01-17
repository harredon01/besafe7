<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCargosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cargos', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->integer('user_id')->unsigned()->nullable();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
                        $table->integer('category_id')->unsigned();
                        $table->foreign('category_id')->references('id')
                                ->on('categories');
                        $table->integer('route_id')->unsigned()->nullable();
                        $table->foreign('route_id')->references('id')
                                ->on('routes')->nullable();
                        $table->string('from_city_name');
                        $table->integer('from_city_id')->unsigned()->nullable();
                        $table->foreign('from_city_id')->references('id')
                                ->on('cities');
                        $table->string('from_region_name');
                        $table->integer('from_region_id')->unsigned()->nullable();
                        $table->foreign('from_region_id')->references('id')
                                ->on('regions')->onDelete('cascade');
                        $table->string('from_country_name');
                        $table->integer('from_country_id')->unsigned()->nullable();
                        $table->foreign('from_country_id')->references('id')
                                ->on('countries')->onDelete('cascade');
                        $table->string('to_city_name');
                        $table->integer('to_city_id')->unsigned()->nullable();
                        $table->foreign('to_city_id')->references('id')
                                ->on('cities');
                        $table->string('to_region_name');
                        $table->integer('to_region_id')->unsigned()->nullable();
                        $table->foreign('to_region_id')->references('id')
                                ->on('regions');
                        $table->string('to_country_name');
                        $table->integer('to_country_id')->unsigned()->nullable();
                        $table->foreign('to_country_id')->references('id')
                                ->on('countries');
                        $table->integer('vehicle_id')->unsigned()->nullable();
                        $table->foreign('vehicle_id')->references('id')
                                ->on('vehicles');
                        $table->double('width');
                        $table->double('offer');
                        $table->double('length');
                        $table->double('height');
                        $table->double('weight');
                        $table->string('description');
                        $table->string('image');
                        $table->dateTime('arrival');
                        $table->integer('status');
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
		Schema::drop('cargos');
	}

}
