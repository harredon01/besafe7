<?php

use Illuminate\Database\Seeder;
use App\Services\Simi;
use App\Services\PayU;
use App\Services\EditOrderFood;
use App\Services\EditAlerts;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Route;
use App\Jobs\ApprovePayment;
use App\Models\Payment;

class PayuSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    const ORDER_PAYMENT = 'order_payment';

    /**
     * The edit profile implementation.
     *
     */
    protected $editOrderFood;

    /**
     * The edit profile implementation.
     *
     */
    protected $payu;

    public function __construct(PayU $payu) {
        $this->payu = $payu;
    }

    public function run() {
        echo env('PAYU_ACCOUNT').PHP_EOL;
        //$this->payu->checkOrders();
    }

}
