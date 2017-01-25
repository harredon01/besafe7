<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cities', function(Blueprint $table) {
            $table->increments('id');
            $table->double('lat', 18, 13);
            $table->double('long', 18, 13);
            $table->index('lat');
            $table->index('long');
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->index('code');
            $table->string('facebook_id')->unique()->nullable();
            $table->string('facebook_country_id')->unique()->nullable();
            $table->integer('region_id')->unsigned()->nullable();
            $table->foreign('region_id')->references('id')
                    ->on('regions')->onDelete('cascade');
            $table->integer('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')
                    ->on('countries')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('cities');
    }

}
