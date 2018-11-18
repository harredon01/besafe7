<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStopsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stops', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->integer('stop_order');
                        $table->integer('amount')->unsigned()->nullable();
                        $table->integer('route_id')->unsigned()->nullable();
                        $table->foreign('route_id')->references('id')
                                ->on('routes');
                        $table->integer('city_id')->unsigned()->nullable();
                        $table->foreign('city_id')->references('id')
                                ->on('cities');
                        $table->integer('address_id')->unsigned()->nullable();
                        $table->foreign('address_id')->references('id')
                                ->on('order_addresses');
                        $table->string('region_name');
                        $table->integer('region_id')->unsigned()->nullable();
                        $table->foreign('region_id')->references('id')
                                ->on('regions');
                        $table->string('country_name');
                        $table->integer('country_id')->unsigned()->nullable();
                        $table->foreign('country_id')->references('id')
                                ->on('countries');
                        $table->dateTime('arrival');
                        $table->text('details');
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
		Schema::drop('stops');
	}

}
