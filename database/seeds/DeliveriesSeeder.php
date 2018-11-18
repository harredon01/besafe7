<?php

use Illuminate\Database\Seeder;
use App\Services\EditOrderFood;
use App\Services\Rapigo;
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
    protected $rapigo;

    public function __construct(EditOrderFood $editOrderFood, Rapigo $rapigo) {
        $this->editOrderFood = $editOrderFood;
        $this->rapigo = $rapigo;
    }

    public function run() {
        /*$points = [];
        $address = [
            "address"=>"Calle 73 # 0 - 24",
            "description" => "prueba 1",
            "type" =>"point",
            "phone" =>"11111111111"
        ];
        array_push($points, $address);
        /*$address = [
            "address"=>"Cra 7 # 64 - 44",
            "description" => "prueba 2",
            "type" =>"point",
            "phone" =>"222222"
        ];
        array_push($points, $address);
        $data['points'] = json_encode($points);
        $query = "https://test.rapigo.co/api/bogota/estimate/";
        $response = $this->rapigo->sendPost($data, $query);
        dd($response);*/
        $this->deleteOldData();
        $this->generateRandomDeliveries();
        $this->editOrderFood->prepareRoutingSimulation(4.670129, -74.051013);
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

    public function generateRandomDeliveries() {
        $this->editOrderFood->generateRandomDeliveries(4.670129, -74.051013);
    }

}
