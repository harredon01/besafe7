<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlocksTable extends Migration {

    /**
     * Run the migrations. 
     *
     * @return void
     */
    public function up() {
        Schema::create('blocks', function(Blueprint $table) {
            $table->increments('id');
            $table->string('network');
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
        Schema::drop('blocks');
    }

}
