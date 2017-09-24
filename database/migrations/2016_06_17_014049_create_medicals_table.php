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
            $table->string('gender')->nullable();
            $table->date('birth')->nullable();
            $table->string('weight')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('antigen')->nullable();
            $table->longText('surgical_history')->nullable();
            $table->longText('obstetric_history')->nullable();
            $table->longText('medications')->nullable();
            $table->longText('alergies')->nullable();
            $table->longText('immunization_history')->nullable();
            $table->longText('medical_encounters')->nullable();
            $table->longText('prescriptions')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('relationship')->nullable();
            $table->string('number')->nullable();
            $table->string('other')->nullable();
            $table->string('eps')->nullable();
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
