<?php

namespace App\Services;

use App\Models\Stop;
use App\Models\Route;
use App\Models\Delivery;
use Illuminate\Support\Facades\Mail;
use App\Mail\StopFailed;

class Basilikum {
    const ROUTE_HOUR_COST = 11000;
    const ROUTE_HOURS_EST = 3;

    public function getEstimate(array $points) {
        //dd($points);
//        $data['points'] = json_encode($points);
//        $query = env('RAPIGO_TEST') . "api/bogota/estimate/";
        //dd($query);
        $response['price2'] = 30000;
        $response['price'] = 30000;
        return $response;
    }

    public function getOrderShippingPrice(array $origin, array $destination) { 
        $response['price'] = 25000;
        $response['price2'] = 25000;
        $response['status']="success";
        return $response;
    }

    public function createRoute(array $points,$route,$stops){
        $route->unit_cost = 0;
        $route->provider_id = "route_".$route->id;
        $location = [
            "runner" => "",
            "runner_phone" => "",
            "lat" => 0,
            "long" => 0
        ];
        $serviceBookResponse["location"] = $location;
        $route->coverage = json_encode($serviceBookResponse);
        $i = 0;
        foreach ($stops as $stop) {
            $totals = $stop->totals;
            $deliveries = $stop->deliveries;
            unset($stop->totals);
            unset($stop->deliveries);
            $stop->code = "stop_".$stop->id;
            $stop->status = "pending";
            $stop->save();
            $stop->totals = $totals;
            $stop->deliveries = $deliveries;
            $i++;
        }
        $route->status = "built";
        $route->save();
        $route->stops = $stops;
        return $route;
    }

    public function checkAddress($address) {
//        $data['address'] = $address;
//        $query = env('RAPIGO_TEST') . "api/bogota/validate_address/";
//        $response = $this->sendPost($data, $query);
        return true;
    }

    public function checkStatus($key) {
//        $data['key'] = $key;
//        $query = env('RAPIGO_TEST') . "api/bogota/get_service_status/";
//        $response = $this->sendPost($data, $query);
        return true;
    }

    private function generateHash($id, $created_at ) {
        return base64_encode(Hash::make($id . $created_at . env('LONCHIS_KEY')));
    }

}
