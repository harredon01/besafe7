<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('type')->nullable();
            $table->text('body')->nullable();
            $table->string('status')->nullable();
            $table->string('pagetitle')->nullable();
            $table->string('metadescription')->nullable();
            $table->string('slug')->nullable();
            $table->text('attributes')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('file_id')->unsigned()->nullable();
            $table->foreign('file_id')->references('id')
                    ->on('files');
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
        Schema::dropIfExists('articles');
    }
}
