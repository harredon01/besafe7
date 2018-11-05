<?php

use Illuminate\Database\Seeder;
use App\Services\EditOrderFood;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

class DeliveriesSeeder extends Seeder {
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
        $this->deleteOldData();
        $this->generateRandomDeliveries();
        $this->editOrderFood->prepareRoutingSimulation(4.670129, -74.051013);
    }
    
    public function deleteOldData() {
        Delivery::where("user_id",1)->delete();
        $routes = Route::where("status","pending")->get();
        foreach ($routes as $value) {
            $value->stops()->delete();
        }
        $routes = Route::where("status","pending")->delete();
        
        OrderAddress::where("name","test")->delete();
    }

    public function generateRandomDeliveries() {
        $this->editOrderFood->generateRandomDeliveries(4.670129, -74.051013);
    }

}
