<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoutesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('routes', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->string('description');
                        $table->integer('vehicle_id')->unsigned()->nullable();
                        $table->foreign('vehicle_id')->references('id')
                                ->on('vehicles');
                        $table->integer('user_id')->unsigned()->nullable();
                        $table->foreign('user_id')->references('id')
                                ->on('users');
                        $table->double('weight');
                        $table->double('length');
                        $table->double('height');
                        $table->double('width');
                        $table->double('unit_price');                        
                        $table->integer('unit');
                        $table->string('status');
			$table->text('coverage')->nullable();
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
		Schema::drop('routes');
	}

}
