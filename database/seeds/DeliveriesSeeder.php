<?php

use Illuminate\Database\Seeder;
use App\Services\Food;
use App\Services\PayU;
use App\Services\EditOrderFood;
use App\Services\EditAlerts;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

class DeliveriesSeeder extends Seeder {

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

    public function __construct(Food $food, EditOrderFood $editOrderfood, PayU $payu, EditAlerts $editAlerts) {
        $this->food = $food;
        $this->editOrderfood = $editOrderfood;
    }

    public function run() {
        
//        $user = User::find(1);
//        $user2 = User::find(2);
//        $users = [$user2];
//        $data = [
//                        "trigger_id" => $user->id,
//                        "message" => "test",
//                        "payload" => ['order_id'=>1,"order_total"=>12,"order_status"=>"test"],
//                        "object" => "test",
//                        "sign" => true,
//                        "type" => self::ORDER_PAYMENT,
//                        "user_status" => "test"
//                    ];
//        $this->food->sendMassMessage($data, $users, $user, true, null,"food");
        $this->food->runCompleteSimulation();
//        $this->food->importDishes();
//        $this->deleteOldData();
//        $this->food->generateRandomDeliveries(4.670129, -74.051013);
//        $this->food->prepareRoutingSimulation(4.670129, -74.051013);
    }

    public function deleteOldData() {
        $deliveries = Delivery::where("user_id", 1)->get();
        foreach ($deliveries as $item) {
            DB::table('delivery_stop')
                    ->where('delivery_id', $item->id)
                    ->delete();
            DB::table('delivery_route')
                    ->where('delivery_id', $item->id)
                    ->delete();
            $item->delete();
        }
        $routes = Route::where("status", "pending")->get();
        foreach ($routes as $value) {
            $value->stops()->delete();
        }
        $routes = Route::where("status", "pending")->delete();

        OrderAddress::where("name", "test")->delete();
    }

}
