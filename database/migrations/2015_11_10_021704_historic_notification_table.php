<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HistoricNotificationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('historic_notification', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->string('subject');
                        $table->string('message');
                        $table->string('payload');
                        $table->string('type');
                        $table->integer('trigger_id')->unsigned();
                        $table->index('trigger_id');
                        $table->string('priority');
                        $table->integer('user_id')->unsigned();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
                        $table->string('status');
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
		Schema::drop('historic_notification');
	}

}
