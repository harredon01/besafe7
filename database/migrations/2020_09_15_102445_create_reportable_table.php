<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportables', function (Blueprint $table) {
            $table->id();
            $table->integer('report_id')->unsigned();
            $table->foreign('report_id')->references('id')
                    ->on('reports')->onDelete('cascade');
            $table->morphs('reportable');
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
        Schema::dropIfExists('reportables');
    }
}
