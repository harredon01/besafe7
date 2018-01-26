<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('reports', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned()->nullable();
            $table->foreign('group_id')->references('id')
                ->on('groups');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->index('type');
            $table->string('object')->nullable();
            $table->boolean('submitted')->default(0);
            $table->boolean('private')->nullable();
            $table->boolean('anonymous')->nullable();
            $table->string('email')->nullable();
            $table->string('hash')->nullable();
            $table->string('telephone')->nullable();
            $table->string('address');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->double('lat', 12, 9);
            $table->double('long', 12, 9);
            $table->index('lat');
            $table->index('long');
            $table->integer('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')
                    ->on('cities');
            $table->integer('region_id')->unsigned()->nullable();
            $table->foreign('region_id')->references('id')
                    ->on('regions');
            $table->integer('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')
                    ->on('countries');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')
                    ->on('users');
            $table->double('minimum', 15, 2)->nullable();
            $table->dateTime('report_time')->nullable();
            $table->string('status')->nullable();
            $table->string('plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('reports');
    }

}
