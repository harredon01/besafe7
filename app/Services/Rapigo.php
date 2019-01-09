<?php

namespace App\Services;

use Validator;
use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use App\Models\City;
use App\Models\Plan;
use App\Models\Country;
use App\Models\Region;
use App\Jobs\ApprovePayment;
use App\Jobs\DenyPayment;
use App\Jobs\PendingPayment;
use App\Models\Source;
use Carbon\Carbon;
use App\Models\Subscription;
use App\Models\Transaction;

class Rapigo {

    public function sendPost(array $data, $query) {
        //url-ify the data for the POST
        $fields_string = "";
        foreach ($data as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $curl = curl_init($query);
        //dd($data);
        $headers = array(
            'Accept: application/json',
            'Authorization: Basic ' . env('RAPIGO_KEY')
        );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode(str_replace("\\", "", $response), true);
        return $response;
    }

    public function getEstimate(array $points) {
        //dd($points);
        $data['points'] = json_encode($points);
        $query = env('RAPIGO_TEST') . "api/bogota/estimate/";
        //dd($query);
        $response = $this->sendPost($data, $query);
        return $response;
    }

    public function getOrderShippingPrice(array $origin, array $destination) {
        //dd($points);
        $points = [];
        $querystop = [
            "address" => $origin['address'],
            "description" => "Origen",
            "type" => "point",
            "phone" => $origin['phone']
        ];
        array_push($points, $querystop);
        $querystop = [
            "address" => $destination['address'],
            "description" => "Destino",
            "type" => "point",
            "phone" => $destination['phone']
        ];
        array_push($points, $querystop);
        $data['points'] = json_encode($points);
        $query = env('RAPIGO_TEST') . "api/bogota/estimate/";
        //dd($query);
        $response = $this->sendPost($data, $query);
        return $response;
    }

    public function createRoute(array $points,$type) {
        if($type == "hour"){
            $data['type'] = 'hour';
        }
        $data['points'] = json_encode($points);
        $query = env('RAPIGO_TEST') . "api/bogota/request_service/";
        $response = $this->sendPost($data, $query);
        return $response;
    }

    public function checkAddress($address) {
        $data['address'] = $address;
        $query = env('RAPIGO_TEST') . "api/bogota/validate_address/";
        $response = $this->sendPost($data, $query);
        return $response;
    }

    public function checkStatus($key) {
        $data['key'] = $key;
        $query = env('RAPIGO_TEST') . "api/bogota/get_service_status/";
        $response = $this->sendPost($data, $query);
        return $response;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function webhook(array $data) {
        $file = '/home/hoovert/access.log';
        // Open the file to get existing content
        $current = file_get_contents($file);
        //$daarray = json_decode(json_encode($data));
        // Append a new person to the file

        $current .= json_encode($data);
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        file_put_contents($file, $current);
        $ApiKey = env('PAYU_KEY');
        $merchant_id = $data['merchant_id'];
        $referenceCode = $data['reference_sale'];
        $TX_VALUE = $data['value'];
        $New_value = number_format($TX_VALUE, 1, '.', '');
        $currency = $data['currency'];
        $transactionState = $data['state_pol'];
        $firma_cadena = "$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
        $firmacreada = md5($firma_cadena);
        $firma = $data['sign'];
        if (strtoupper($firma) == strtoupper($firmacreada)) {
            
        } else {
            
        }
    }

}
