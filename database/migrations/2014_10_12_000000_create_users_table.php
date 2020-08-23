<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable()->unique();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('cellphone');
            $table->index('cellphone');
            $table->string('area_code');
            $table->index('area_code');
            $table->string('gender')->nullable();
            $table->string('name')->nullable();
            $table->string('docType')->nullable();
            $table->string('docNum')->nullable();
            $table->boolean('emailNotifications')->default(false);
            $table->boolean('pushNotifications')->default(false);
            $table->boolean('optinMarketing')->default(false);
            $table->string('green')->nullable();
            $table->string('red')->nullable(); 
            $table->boolean('is_alerting')->default(false);
            $table->boolean('is_tracking')->default(false);
            $table->boolean('write_report')->default(false);
            $table->string('alert_type')->nullable();
            $table->string('notify_location')->nullable();
            $table->string('plan')->nullable();
            $table->string('hash')->nullable();
            $table->integer('trip')->unsigned()->nullable();
            $table->index('trip');
            $table->string('token')->nullable();
            $table->string('language')->nullable();
            $table->string('avatar')->default("");
            $table->string('code')->nullable();
            $table->string('platform')->nullable();
            $table->string('two_factor_token')->nullable();
            $table->timestamp('two_factor_expiry')->nullable();
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
    public function down() {
        Schema::drop('users');
    }

}
