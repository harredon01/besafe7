<?php

use Illuminate\Database\Seeder;
use App\Services\EditOrderFood;
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
    protected $editOrderFood;

    public function __construct(EditOrderFood $editOrderFood) {
        $this->editOrderFood = $editOrderFood;
    }

    public function run() {
//        $this->deleteOldData();
//        $this->generateRandomDeliveries();
        $deliveries = Delivery::where("status","enqueue")->get();
        //$this->editOrderFood->getPurchaseOrder($deliveries);
        $this->editOrderFood->buildScenario("preorganize",null);
    }
    
    public function deleteOldData() {
        OrderAddress::where("name","test")->delete();
        Delivery::where("user_id",1)->delete();
    }

    public function generateRandomDeliveries() {
        $this->editOrderFood->generateRandomDeliveries(4.670129, -74.051013);
    }

}
