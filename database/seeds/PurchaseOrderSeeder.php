<?php

use Illuminate\Database\Seeder;
use App\Services\Food;
use App\Models\Delivery;
use App\Models\OrderAddress;

class PurchaseOrderSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */

    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(Food $food) {
        $this->food = $food;
    }

    public function run() {
//        $this->deleteOldData();
//        $this->food->generateRandomDeliveries(4.670129, -74.051013);;
        $deliveries = Delivery::where("status","enqueue")->get();
        $this->food->getPurchaseOrder($deliveries);
        $this->food->buildScenario("preorganize",null);
    }
    
    public function deleteOldData() {
        OrderAddress::where("name","test")->delete();
        Delivery::where("user_id",1)->delete();
    }

}
