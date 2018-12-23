<?php

use Illuminate\Database\Seeder;
use App\Services\OrderJobs;
use App\Services\PayU;
use App\Services\EditOrderFood;
use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

class RecurringPaymentsSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */

    /**
     * The edit profile implementation.
     *
     */
    protected $orderJobs;

    public function __construct(OrderJobs $orderJobs ) {
        $this->orderJobs = $orderJobs;
    }

    public function run() {

        $order = Order::find(70);
        $ip = gethostbyname(env('APP_URL'));
        $result = $this->orderJobs->RecurringOrder($order,$ip);
        //dd($result);
    }

}
