<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('regions', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->index('code');
            $table->string('facebook_id')->unique()->nullable();
            $table->string('facebook_country_id')->unique()->nullable();
            $table->integer('country_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('country_id')->references('id')
                    ->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('regions');
    }

}
