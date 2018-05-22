<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProductVariantsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_variant', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->integer('product_id')->unsigned()->nullable();
                        $table->foreign('product_id')->references('id')
                                ->on('products');
                        $table->string('sku');
                        $table->boolean('isActive');
                        $table->boolean('requires_authorization');
                        $table->boolean('is_shippable');
                        $table->boolean('is_digital');
                        $table->string('ref2');
                        $table->string('type');
                        $table->string('description');
                        $table->double('price', 15, 2);
                        $table->double('sale', 15, 2);
                        $table->double('tax', 15, 2);
                        $table->integer('quantity');
                        $table->text('attributes'); 
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
                Schema::drop('product_variant');
	}

}
