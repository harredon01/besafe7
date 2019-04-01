<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('messages', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->binary('message');
                        $table->string('status');
                        $table->string('messageable_type');
                        $table->integer('user_id')->unsigned();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
                        $table->boolean('is_public')->default(false);
                        $table->integer('messageable_id')->unsigned()->nullable();
                        $table->index('messageable_id');
                        $table->integer('target_id')->unsigned()->nullable();
                        $table->index('target_id');
                        $table->string('priority');
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
		Schema::drop('messages');
	}

}
