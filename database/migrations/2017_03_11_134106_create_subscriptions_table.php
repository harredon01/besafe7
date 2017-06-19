<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source_id')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('name')->nullable();
            $table->string('other')->nullable();
            $table->integer('plan_id')->nullable();
            $table->string('plan')->nullable();
            $table->string('gateway')->nullable();
            $table->integer('user_id');
            $table->string('client_id')->nullable();
            $table->integer('object_id')->nullable();
            $table->string('interval')->nullable();
            $table->string('interval_type')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('subscriptions');
    }

}
