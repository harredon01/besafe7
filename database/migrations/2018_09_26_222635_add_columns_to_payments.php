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
            $table->integer('address_id')->unsigned()->nullable();
            $table->foreign('address_id')->references('id')
                                ->on('order_addresses');
            $table->string('status');
            $table->string('referenceCode');
            $table->string('transactionId');
            $table->string('responseCode');
            $table->text('attributes');
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
            $table->dropForeign('payments_user_id_foreign');
            $table->dropColumn('user_id');
            $table->dropForeign('payments_order_id_foreign');
            $table->dropColumn('order_id');
            $table->dropForeign('payments_address_id_foreign');
            $table->dropColumn('address_id');
            $table->dropColumn('status');
            $table->dropColumn('referenceCode');
            $table->dropColumn('responseCode');
            $table->dropColumn('transactionId');
            $table->dropColumn('total');
            $table->dropColumn('tax');
            
        });
    }
}
