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
	    $table->integer('emailNotifications')->default(0);
	    $table->integer('pushNotifications')->default(0);
	    $table->integer('green')->nullable();
	    $table->integer('red')->nullable();
	    $table->integer('is_alerting')->default(0);
	    $table->integer('is_tracking')->default(0);
	    $table->string('alert_type')->nullable();
	    $table->string('notify_location')->nullable();
            $table->string('lastName');
	    $table->string('cellphone');
	    $table->index('cellphone');
	    $table->string('area_code');
	    $table->index('area_code');
	    $table->string('hash')->nullable();
	    $table->integer('trip')->unsigned()->nullable();
            $table->index('trip');
	    $table->string('token')->nullable();
	    $table->string('platform')->nullable();
	    $table->string('name')->nullable();
	    $table->string('docType')->nullable();
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
