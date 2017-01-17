<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersSocialTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users_social', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('username')->nullable();
                        $table->string('email')->nullable();
			$table->string('avatar');
			$table->string('provider');
			$table->string('provider_id')->unique();
			$table->rememberToken();
			$table->timestamps();
                        $table->integer('user_id')->unsigned();
                        $table->foreign('user_id')->references('id')
                                ->on('users')->onDelete('cascade');
		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users_social');
	}

}
