<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_product', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('group_id')->unsigned();
            $table->foreign('group_id')->references('id')
                    ->on('groups');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')
                    ->on('products');
            $table->timestamp('last_significant')->nullable();
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
        Schema::dropIfExists('group_product');
    }
}
