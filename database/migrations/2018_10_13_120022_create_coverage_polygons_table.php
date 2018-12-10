<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoveragePolygonsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('coverage_polygons', function (Blueprint $table) {
            $table->increments('id');
            $table->text('coverage')->nullable();
            $table->integer('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')
                    ->on('countries');
            $table->integer('region_id')->unsigned()->nullable();
            $table->foreign('region_id')->references('id')
                    ->on('regions');
            $table->integer('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')
                    ->on('cities');
            $table->integer('merchant_id')->unsigned()->nullable();
            $table->foreign('merchant_id')->references('id')
                    ->on('merchants');
            $table->integer('address_id')->unsigned()->nullable();
            $table->foreign('address_id')->references('id')
                    ->on('addresses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('coverage_polygons');
    }

}
