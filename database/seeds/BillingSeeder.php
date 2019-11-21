<?php

use Illuminate\Database\Seeder;
use App\Services\MercadoPagoService;
use App\Services\EditOrder;
use App\Services\EditBilling;
use App\Jobs\ApprovePayment;
use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

class BillingSeeder extends Seeder {

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
    protected $mercadoPago;

    /**
     * The edit profile implementation.
     *
     */
    protected $billing;

    public function __construct(EditBilling $billing, MercadoPagoService $mercado) {
        $this->billing = $billing;
        $this->mercadoPago = $mercado;
    }

    public function run() {
        dd($this->mercadoPago->getOffsite());

        $payment = Payment::find(630);
        $data = ["payment_id"=>630];
        $user = User::find(2);
        $source = "MercadoPago";
        //dispatch(new ApprovePayment($payment, "Food"));
        //$this->billing->payCreditCard($user, $source, $data);
        
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
//        $this->food->runCompleteSimulation();
//        $this->food->importDishes();
//        $this->deleteOldData();
//        $this->food->generateRandomDeliveries(4.670129, -74.051013);
//        $this->food->prepareRoutingSimulation(4.670129, -74.051013);
    }

}
