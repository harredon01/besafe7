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
    protected $food;

    public function __construct(PayU $proofhub) {
        $this->proofhub = $proofhub;
    }

    public function run() {
        $this->proofhub->sendGet("https://maps.googleapis.com/maps/api/place/findplacefromtext/json?key=AIzaSyDMggl2vpY-NVNQNNqj8yAFUcbgSYI9wss&input=Carrera 7&inputtype=textquery&language=es&fields=formatted_address,geometry,icon,id,name&locationbias=circle:53000@4.694278,-74.067352");
    }

}
