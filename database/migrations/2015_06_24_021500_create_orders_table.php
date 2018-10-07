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
                        $table->string('merchant_status')->nullable();
                        $table->string('owner_status')->nullable();
                        $table->string('payment_status')->nullable();
                        $table->string('execution_status')->nullable();
                        $table->string('referenceCode');
                        $table->text('attributes');
                        $table->string('comments')->nullable();
                        $table->double('subtotal', 15, 2);
                        $table->integer('object_id')->unsigned()->nullable();
                        $table->index('object_id');
                        $table->string('type')->nullable();
                        $table->double('tax', 15, 2);
                        $table->double('shipping', 15, 2);
                        $table->double('discount', 15, 2);
                        $table->double('total', 15, 2);
                        $table->boolean('is_shippable');
                        $table->boolean('is_digital');
                        $table->boolean('is_editable')->nullable();
                        $table->boolean('requires_authorization');
                        $table->integer('payment_method_id')->unsigned()->nullable();
                        $table->foreign('payment_method_id')->references('id')
                                ->on('payment_methods');
                        $table->integer('user_id')->unsigned()->nullable();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
                        $table->integer('supplier_id')->unsigned()->nullable();
                        $table->index('supplier_id');
                        $table->text('extras')->nullable();
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
		Schema::drop('orders');
	}

}
