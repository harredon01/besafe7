<?php

use Illuminate\Database\Seeder;
use App\Services\Food;
use App\Services\EditOrderFood;
use App\Models\Item;
use App\Models\Order;
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
    
    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(Food $food,EditOrderFood $editOrderfood) {
        $this->food = $editOrderfood;
        $this->editOrderfood = $editOrderfood;
    }

    public function run() {
//        $this->deleteOldData(); 
//        $this->food->reprogramDeliveries();
//        $item = Item::find(49);
//        $this->food->createDeliveries(1,$item,1741);
        $order = Order::find(53);
        $this->food->approveOrder($order);
//        $users = [2];
//        $this->food->checkUsersCredits($users);
//        $this->food->generateRandomDeliveries(4.670129, -74.051013);
//        $this->food->prepareRoutingSimulation(4.670129, -74.051013);
    }
    
    public function deleteOldData() {
        $deliveries = Delivery::where("user_id",1)->get();
        foreach ($deliveries as $item) {
            DB::table('delivery_stop')
                     ->where('delivery_id', $item->id)
                     ->delete();
            DB::table('delivery_route')
                     ->where('delivery_id', $item->id)
                     ->delete();
            $item->delete();
        }
        $routes = Route::where("status","pending")->get();
        foreach ($routes as $value) {
            $value->stops()->delete();
        }
        $routes = Route::where("status","pending")->delete();
        
        OrderAddress::where("name","test")->delete();
    }
}
