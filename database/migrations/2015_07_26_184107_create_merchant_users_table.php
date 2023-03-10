<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('merchant_user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('merchant_id')->unsigned();
                        $table->foreign('merchant_id')->references('id')
                                ->on('merchants')->onDelete('cascade');
                        $table->integer('user_id')->unsigned();
                        $table->foreign('user_id')->references('id')
                                ->on('users')->onDelete('cascade');
                        
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
		Schema::drop('merchant_user');
	}

}
