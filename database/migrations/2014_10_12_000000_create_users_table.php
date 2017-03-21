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
            $table->string('firstName');
            $table->boolean('emailNotifications')->default(false);
            $table->boolean('pushNotifications')->default(false);
            $table->string('green')->nullable();
            $table->string('red')->nullable();
            $table->boolean('is_alerting')->default(false);
            $table->boolean('is_tracking')->default(false);
            $table->boolean('write_report')->default(false);
            $table->string('alert_type')->nullable();
            $table->string('notify_location')->nullable();
            $table->string('lastName');
            $table->string('cellphone');
            $table->index('cellphone');
            $table->string('area_code');
            $table->index('area_code');
            $table->string('stripe_id')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('hash')->nullable();
            $table->integer('trip')->unsigned()->nullable();
            $table->index('trip');
            $table->string('gender')->nullable();
            $table->string('token')->nullable();
            $table->string('avatar')->default("");
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
    public function down() {
        Schema::drop('users');
    }

}
