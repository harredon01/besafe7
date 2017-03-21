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
                        $table->string('type');
                        $table->string('avatar')->nullable();
                        $table->timestamp('ends_at')->nullable();
                        $table->boolean('is_public')->default(false);
                        $table->integer('admin_id')->unsigned();
                        $table->foreign('admin_id')->references('id')
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
		Schema::drop('groups');
	}

}
