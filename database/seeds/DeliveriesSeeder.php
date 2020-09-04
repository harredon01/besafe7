<?php

use Illuminate\Database\Seeder;
use App\Services\Security;
use App\Services\EditAlerts;
use App\Jobs\ApprovePayment;
use App\Models\Payment;
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
    protected $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    public function run() {
        $geolocation = app('Geolocation');
        $result = $geolocation->checkMerchantPolygons(4.6559523, -74.050506, 1299, "Rapigo");
        dd($result);
        $this->security->generateCertificate();
//        $data = [
//            "address" => "Calle 73 # 0 - 24",
//            "complete" => false, 
//            "merchant_id" => 1299,
//            "provider" => "Basilikum"
//        ];
//        $deliveries = Delivery::where("status","completed")->where("delivery",">","2020-03-08")->get();
//        
//        //dispatch(new ApprovePayment($payment, "Food"));
//        foreach ($deliveries as $delivery) {
//            $this->security->addInfoToDelivery($delivery);
//        }
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
//        $this->security->sendMassMessage($data, $users, $user, true, null,"security");
//        $this->security->runCompleteSimulation();
//        $this->security->importDishes();
//        $this->deleteOldData();
//        $this->security->generateRandomDeliveries(4.670129, -74.051013);
//        $this->security->prepareRoutingSimulation(4.670129, -74.051013);
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
