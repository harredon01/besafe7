<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('reports', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->index('type');
            $table->string('object')->nullable();
            $table->boolean('submitted')->default(0);
            $table->boolean('private')->nullable();
            $table->boolean('anonymous')->nullable();
            $table->string('email')->nullable();
            $table->string('slug')->nullable();
            $table->index('slug');
            $table->string('telephone')->nullable();
            $table->string('address');
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->text('attributes')->nullable();
            $table->string('icon')->nullable();
            $table->double('lat', 12, 9)->nullable();
            $table->double('long', 12, 9)->nullable();
            $table->point('position')->nullable();
            $table->index('lat');
            $table->index('long');
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
            $table->dateTime('report_time')->nullable();
            $table->string('status')->nullable();
            $table->string('plan')->nullable();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE reports ADD FULLTEXT fulltext_index_report (name, type, email,description,attributes,keywords)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('reports');
    }

}
