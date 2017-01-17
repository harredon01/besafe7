<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficeHoursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('office_hours', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->integer('day');
                        $table->time('open');
                        $table->time('close');
                        $table->integer('merchant_id')->unsigned()->nullable();
                        $table->foreign('merchant_id')->references('id')
                                ->on('merchants');
                        
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
		Schema::drop('office_hours');
	}

}
