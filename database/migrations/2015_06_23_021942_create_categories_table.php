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
                        $table->string('type')->nullable();
                        $table->string('level')->nullable();
                        $table->string('icon')->nullable();
                        $table->string('description')->nullable();
                        $table->integer('parent_id')->unsigned()->nullable();
                        $table->index('parent_id');
                        $table->boolean('isActive');
                        $table->string('url');
                        $table->index('url');
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
