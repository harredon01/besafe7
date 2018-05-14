<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_product', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('merchant_id')->unsigned();
            $table->foreign('merchant_id')->references('id')
                    ->on('merchants');
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
        Schema::dropIfExists('merchant_product');
    }
}
