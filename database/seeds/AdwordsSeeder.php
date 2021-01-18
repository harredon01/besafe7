<?php

use Illuminate\Database\Seeder;
use App\Services\Adwords;

use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

class AdwordsSeeder extends Seeder {

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
    protected $adwords;

    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(Adwords $adwords) {
        $this->adwords = $adwords;
    }

    public function run() {
        $this->adwords->run();
    }

}
