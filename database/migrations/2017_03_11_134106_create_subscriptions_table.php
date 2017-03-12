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
            $table->integer('user_id');
            $table->integer('group_id');
            $table->integer('order_id');
            $table->string('type');
            $table->string('name');
            $table->text('other');
            $table->string('plan_id');
            $table->string('plan');
            $table->integer('quantity');
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
