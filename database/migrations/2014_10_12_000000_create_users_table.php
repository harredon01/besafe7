<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
	    $table->increments('id');
            $table->string('firstName');
	    $table->integer('emailNotifications');
	    $table->integer('pushNotifications');
	    $table->integer('green');
	    $table->integer('red');
	    $table->integer('is_alerting');
	    $table->integer('is_tracking');
	    $table->string('alert_type');
	    $table->string('notify_location');
            $table->string('lastName');
	    $table->string('cellphone');
	    $table->index('cellphone');
	    $table->string('area_code');
	    $table->index('area_code');
	    $table->string('hash');
	    $table->integer('trip')->unsigned();
            $table->index('trip');
	    $table->string('token');
	    $table->string('platform');
	    $table->string('name');
	    $table->string('docType');
	    $table->string('docNum')->nullable()->unique();
            $table->string('email')->nullable()->unique();
	    $table->string('username')->nullable();
	    $table->string('password');
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
