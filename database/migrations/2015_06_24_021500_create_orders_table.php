<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->string('status');
                        $table->string('referenceCode');
                        $table->string('comments')->nullable();
                        $table->double('subtotal', 15, 2);
                        $table->double('tax', 15, 2);
                        $table->double('shipping', 15, 2);
                        $table->double('discount', 15, 2);
                        $table->double('total', 15, 2);
                        $table->boolean('is_shippable');
                        $table->boolean('is_digital');
                        $table->integer('payment_method_id')->unsigned()->nullable();
                        $table->foreign('payment_method_id')->references('id')
                                ->on('payment_methods');
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
		Schema::drop('orders');
	}

}
