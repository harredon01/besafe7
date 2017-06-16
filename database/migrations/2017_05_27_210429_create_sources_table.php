<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source');
            $table->string('gateway');
            $table->string('client_id');
            $table->string('extra');
            $table->string('type');
            $table->boolean('is_active');
            $table->boolean('has_default');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')
                    ->on('users');
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
        Schema::dropIfExists('sources');
    }
}
