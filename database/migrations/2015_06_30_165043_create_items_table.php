<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('items', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->string('name');
                        $table->string('sku');
                        $table->string('ref2');
                        $table->double('price', 15, 2);
                        $table->integer('quantity');
                        $table->integer('product_variant_id')->unsigned()->nullable();
                        $table->foreign('product_variant_id')->references('id')
                                ->on('product_variant');
                        $table->integer('user_id')->unsigned()->nullable();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
                        $table->integer('order_id')->unsigned()->nullable();
                        $table->foreign('order_id')->references('id')
                                ->on('orders');
                        $table->text('attributes');
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
		Schema::drop('items');
	}

}
