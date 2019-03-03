<?php

namespace App\Services;

use App\Models\Stop;
use App\Models\Route;
use App\Models\Delivery;
use Illuminate\Support\Facades\Mail;
use App\Mail\StopFailed;

class Basilikum {

    public function getEstimate(array $points) {
        //dd($points);
//        $data['points'] = json_encode($points);
//        $query = env('RAPIGO_TEST') . "api/bogota/estimate/";
        //dd($query);
        $response['price2'] = 20000;
        $response['price'] = 20000;
        return $response;
    }

    public function getOrderShippingPrice(array $origin, array $destination) {
        $response['price'] = 20000;
        $response['price2'] = 20000;
        return $response;
    }

    public function createRoute(array $points, $type) {
//        if ($type == "hour") {
//            $data['type'] = 'hour';
//        }
//        $data['points'] = json_encode($points);
//        $query = env('RAPIGO_TEST') . "api/bogota/request_service/";
//        $response = $this->sendPost($data, $query);
        return true;
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

    private function generateHash($id, $created_at, $updated_at) {
        return base64_encode(Hash::make($id . $created_at . $updated_at . env('LONCHIS_KEY')));
    }

}
