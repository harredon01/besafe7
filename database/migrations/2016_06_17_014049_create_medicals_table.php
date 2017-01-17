<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicalsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('medicals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')
                    ->on('users');
            $table->string('gender');
            $table->date('birth');
            $table->string('weight');
            $table->string('blood_type');
            $table->string('antigent');
            $table->longText('surgical_history');
            $table->longText('obstetric_history');
            $table->longText('medications');
            $table->longText('alergies');
            $table->longText('immunization_history');
            $table->longText('medical_encounters');
            $table->longText('prescriptions');
            $table->string('emergency_name');
            $table->string('relationship');
            $table->string('number');
            $table->string('other');
            $table->string('eps');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('medicals');
    }

}
