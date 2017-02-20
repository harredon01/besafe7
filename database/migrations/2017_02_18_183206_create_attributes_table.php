<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->string('type');
            $table->integer('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')
                    ->on('products');
            $table->integer('product_variant_id')->unsigned()->nullable();
            $table->foreign('product_variant_id')->references('id')
                    ->on('product_variant');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('attributes');
    }

}
