<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CategoriesProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('category_product', function(Blueprint $table)
		{
			$table->increments('id');
			
                        $table->integer('product_id')->unsigned();
                        $table->foreign('product_id')->references('id')
                                ->on('products')->onDelete('cascade');
                        $table->integer('category_id')->unsigned();
                        $table->foreign('category_id')->references('id')
                                ->on('categories')->onDelete('cascade');
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
		Schema::drop('category_product');
	}

}
