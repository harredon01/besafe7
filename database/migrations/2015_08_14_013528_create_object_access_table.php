<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObjectAccessTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('userables', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->integer('user_id')->unsigned();
                        $table->integer('object_id')->unsigned()->nullable();
                        $table->index('object_id');
                        $table->string('userable_type')->nullable();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
                        $table->integer('userable_id')->unsigned()->nullable();
                        $table->index('userable_id');
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
		Schema::drop('userables');
	}

}
