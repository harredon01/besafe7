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
            
            $table->string('type')->nullable();
            $table->string('gateway')->nullable();
            $table->string('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('currency')->nullable();
            $table->string('transaction_state')->nullable();
            
            $table->string('reference_sale')->nullable();
            $table->string('response_code')->nullable();
            $table->string('transaction_id')->nullable();
            $table->integer('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')
                    ->on('orders')->onDelete('cascade');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')
                    ->on('users');
            $table->datetime('transaction_date')->nullable();
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
