<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductVariantsAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variant_attribute_option', function (Blueprint $table) {
            $table->increments('id');
            $table->string('valueS')->nullable();
            $table->longText('valueL')->nullable();
            $table->integer('valueI')->nullable();
            $table->double('valueD', 15, 6)->nullable();
            $table->string('type');
            $table->integer('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')
                    ->on('products');
            $table->integer('product_variant_id')->unsigned()->nullable();
            $table->foreign('product_variant_id')->references('id')
                    ->on('product_variant');
            $table->integer('attribute_id')->unsigned()->nullable();
            $table->foreign('attribute_id')->references('id')
                    ->on('attributes');
            $table->integer('attribute_option_id')->unsigned()->nullable();
            $table->foreign('attribute_option_id')->references('id')
                    ->on('attribute_options');
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
        Schema::dropIfExists('product_variant_attribute_option');
    }
}
