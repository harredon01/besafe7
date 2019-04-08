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
use App\Models\OrderAddress;

class SimiSeeder extends Seeder {

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
    protected $food;

    public function __construct(Simi $proofhub) {
        $this->proofhub = $proofhub;
    }

    public function run() {
        $this->proofhub->run();
    }

}
