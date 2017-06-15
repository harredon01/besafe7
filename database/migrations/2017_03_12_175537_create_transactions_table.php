<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('orderId')->nullable();
            $table->string('type')->nullable();
            $table->string('description')->nullable();
            $table->string('currency')->nullable();
            $table->string('gateway')->nullable();
            $table->string('referenceCode')->nullable();
            $table->string('transactionId')->nullable();
            $table->string('state')->nullable();
            $table->integer('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')
                    ->on('orders')->onDelete('cascade');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')
                    ->on('users');
            $table->string('paymentNetworkResponseCode')->nullable();
            $table->string('paymentNetworkResponseErrorMessage', 15, 6)->nullable();
            $table->string('trazabilityCode')->nullable();
            $table->string('authorizationCode')->nullable();
            $table->string('responseMessage')->nullable();
            $table->string('pendingReason')->nullable();
            $table->string('responseCode')->nullable();
            $table->string('errorCode')->nullable();
            $table->string('operationDate')->nullable();
            $table->string('additionalInfo')->nullable();
            $table->datetime('transactionDate')->nullable();
            $table->datetime('transactionTime')->nullable();
            $table->text('extras')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
