<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CartConditionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cart_conditions', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->string('name');
                        $table->string('type');
                        $table->string('target');
                        $table->string('value');
                        $table->string('coupon')->nullable();
                        $table->boolean('isActive');
                        $table->integer('product_id')->unsigned()->nullable();
                        $table->foreign('product_id')->references('id')
                                ->on('products');
                        $table->integer('product_variant_id')->unsigned()->nullable();
                        $table->foreign('product_variant_id')->references('id')
                                ->on('product_variant');
                        $table->integer('city_id')->unsigned()->nullable();
                        $table->foreign('city_id')->references('id')
                                ->on('cities');
                        $table->integer('region_id')->unsigned()->nullable();
                        $table->foreign('region_id')->references('id')
                                ->on('regions');
                        $table->integer('country_id')->unsigned()->nullable();
                        $table->foreign('country_id')->references('id')
                                ->on('countries');
                        $table->text('attributes')->nullable();
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
                Schema::drop('cart_conditions');
	}

}
