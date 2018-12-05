<?php

use Illuminate\Database\Seeder;
use App\Services\Food;
use App\Services\PayU;
use App\Services\EditOrderFood;
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

    public function __construct(Food $food, EditOrderFood $editOrderfood, PayU $payu) {
        $this->food = $payu;
        $this->editOrderfood = $editOrderfood;
    }

    public function run() {
//        $this->deleteOldData(); 
//        $this->food->reprogramDeliveries();
//        $item = Item::find(49);
//        $this->food->createDeliveries(1,$item,1741);
//        $order = Order::find(53);
//        $this->food->approveOrder($order);
        $user = User::find(2);
        $data = [
            "payer_name" => "Hoovert2 Arredondo2",
            "payer_id" => "1020716536",
            "cc_branch" => "VISA",
            "cc_number" => "4111111111111111",
            "cc_security_code" => "123",
            "cc_expiration_month" => "11",
            "cc_expiration_year" => "22",
            "cc_name" => "APPROVED",
            "payer_city" => "BOGOTA",
            "payer_state" => "BOGOTA DC",
            "payer_country" => "CO",
            "payer_postal" => "110221",
            "payer_phone" => "3211336",
            "payer_address" => "Cl. 73 #0 - 24 este Bogotá, Colombia",
            "payer_name" => "Hoovert2 Arredondo2",
            "payer_email" => "harredon01@gmail.com",
            "payer_phone" => "3211336",
            "payer_id" => "1020716536",
        ];
        $result = $this->food->checkOrders();
        dd($result);
//        $result = $this->food->createToken($user,$data);
//        dd($result);
//        $source = $user->sources()->where('gateway', strtolower("PayU"))->first();
//        $result = $this->food->getSources($source);
//        dd($result);
//        $users = [2];
//        $this->food->checkUsersCredits($users);
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
