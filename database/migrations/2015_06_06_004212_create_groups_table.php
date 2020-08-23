<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('groups', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->string('name');
                        $table->string('code')->nullable();
                        $table->string('status');
                        $table->string('plan')->nullable();
                        $table->string('type')->nullable();
                        $table->string('avatar')->nullable();
                        $table->string('level');
                        $table->boolean('is_public')->default(false);
                        $table->integer('max_users')->unsigned()->nullable();
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
		Schema::drop('groups');
	}

}
