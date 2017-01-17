<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoricLocationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('historic_location', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->double('lat', 12, 9);
                        $table->double('long', 12, 9);
                        $table->index('lat');
                        $table->index('long');
                        $table->string('name');
                        $table->string('status');
                        $table->string('speed');
                        $table->string('activity');
                        $table->string('battery');
                        $table->string('islast');
                        $table->integer('trip')->unsigned()->nullable();
                        $table->index('trip');
                        $table->string('phone')->nullable();
                        $table->dateTime('report_time');
                        $table->integer('user_id')->unsigned();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
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
		Schema::drop('historic_location');
	}

}
