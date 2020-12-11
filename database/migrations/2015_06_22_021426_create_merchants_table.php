<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('merchants', function(Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_id')->unique()->nullable();
            $table->index('merchant_id');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->index('type');
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('slug')->nullable();
            $table->index('slug');
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->text('attributes')->nullable();
            $table->text('keywords')->nullable();
            $table->string('icon')->nullable();
            $table->double('lat', 12, 9)->nullable();
            $table->double('long', 12, 9)->nullable();
            $table->point('position')->nullable();
            $table->index('lat');
            $table->index('long');
            $table->boolean('submitted')->default(0);
            $table->boolean('private')->default(false);
            $table->integer('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')
                    ->on('cities');
            $table->integer('region_id')->unsigned()->nullable();
            $table->foreign('region_id')->references('id')
                    ->on('regions');
            $table->integer('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')
                    ->on('countries');
            $table->double('minimum', 15, 2)->nullable();
            $table->string('delivery_time')->nullable();
            $table->double('delivery_price', 15, 2)->nullable();
            $table->decimal('price')->nullable();
            $table->decimal('unit_cost')->nullable();
            $table->decimal('base_cost')->nullable();
            $table->string('unit')->nullable();
            $table->string('currency')->nullable();
            $table->string('status')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('plan')->nullable();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE merchants ADD FULLTEXT fulltext_index_merchant (name, type, email,description,attributes,keywords)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('merchants');
    }

}
