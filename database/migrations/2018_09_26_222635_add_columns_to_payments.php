<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')
                                ->on('users');
            $table->integer('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')
                                ->on('orders');
            $table->integer('payer_id')->unsigned()->nullable();
            $table->index('payer_id');
            $table->integer('buyer_id')->unsigned()->nullable();
            $table->index('buyer_id');
            $table->string('status');
            $table->string('referenceCode');
            $table->string('transactionId');
            $table->string('responseCode');
            $table->double('total', 15, 2);
            $table->double('tax', 15, 2);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('order_id');
            $table->dropColumn('payer_id');
            $table->dropColumn('buyer_id');
            $table->dropColumn('status');
            $table->dropColumn('referenceCode');
            $table->dropColumn('responseCode');
            $table->dropColumn('transactionId');
            $table->dropColumn('total');
            $table->dropColumn('tax');
            
        });
    }
}
