<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('addresses', function(Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->string('address');
            $table->string('notes')->nullable();
            $table->string('city')->nullable();
            $table->string('type')->nullable();
            $table->string('postal')->nullable();
            $table->string('phone')->nullable();
            $table->double('lat', 16, 13)->nullable();
            $table->double('long', 16, 13)->nullable();
            $table->index('lat');
            $table->index('long');
            $table->integer('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')
                    ->on('cities');
            $table->integer('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')
                    ->on('countries');
            $table->integer('region_id')->unsigned()->nullable();
            $table->foreign('region_id')->references('id')
                    ->on('regions');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')
                    ->on('users');
            $table->boolean('is_default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('addresses');
    }

}
