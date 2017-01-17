<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CategoriesMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_merchant', function(Blueprint $table)
		{
			$table->increments('id');
			
                        $table->integer('merchant_id')->unsigned();
                        $table->foreign('merchant_id')->references('id')
                                ->on('merchants')->onDelete('cascade');
                        $table->integer('category_id')->unsigned();
                        $table->foreign('category_id')->references('id')
                                ->on('categories')->onDelete('cascade');
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
        Schema::drop('category_merchant');
    }
}
