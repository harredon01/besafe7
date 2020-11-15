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
                        $table->text('description');
                        $table->string('slug');
                        $table->index('slug');
                        $table->double('high', 15, 2); 
                        $table->double('low', 15, 2);
                        $table->boolean('isActive');
                        $table->integer('user_id')->unsigned()->nullable();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
			$table->timestamps();
		});
                DB::statement('ALTER TABLE products ADD FULLTEXT fulltext_index (name, description)');
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
