<?php

use Illuminate\Database\Seeder;
use App\Services\OrderJobs;
use App\Models\Order;

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
