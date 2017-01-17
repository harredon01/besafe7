<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('categories', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->string('name');
                        $table->string('type');
                        $table->string('level');
                        $table->string('description');
                        $table->integer('parent_id')->unsigned()->nullable();
                        $table->index('parent_id');
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
		Schema::drop('categories');
	}

}
