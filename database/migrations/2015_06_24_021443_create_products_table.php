<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('products', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->string('name');
                        $table->string('description');
                        $table->string('availability');
                        $table->string('hash');
                        $table->integer('object_id')->unsigned()->nullable();
                        $table->index('object_id');
                        $table->string('type')->nullable();
                        $table->boolean('isActive');
                        $table->integer('user_id')->unsigned()->nullable();
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
		Schema::drop('products');
	}

}
