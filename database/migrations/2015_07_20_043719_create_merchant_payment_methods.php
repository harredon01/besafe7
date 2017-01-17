<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantPaymentMethods extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('merchant_payment_methods', function(Blueprint $table)
		{
			$table->increments('id');
			
                        $table->integer('merchant_id')->unsigned();
                        $table->foreign('merchant_id')->references('id')
                                ->on('merchants');
                        $table->integer('payment_method_id')->unsigned();
                        $table->foreign('payment_method_id')->references('id')
                                ->on('payment_methods');
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
		Schema::drop('merchant_payment_methods');
	}

}
