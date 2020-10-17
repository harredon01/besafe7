<?php

namespace App\Services;

use App\Models\Stop;
use App\Models\Route;
use App\Models\User;
use App\Models\City;
use App\Models\Push;
use Illuminate\Support\Facades\Hash;
use App\Models\Delivery;
use Illuminate\Support\Facades\Mail;
use App\Mail\StopFailed;

class MiPaquete {

    const ROUTE_HOUR_COST = 11000;
    const ROUTE_HOURS_EST = 3;

    public function sendPost(array $data, $query, $test) {
        //url-ify the data for the POST
//        $fields_string = "";
//        foreach ($data as $key => $value) {
//            $fields_string .= $key . '=' . $value . '&';
//        }
//        rtrim($fields_string, '&');
        if (false) {
            $url = "https://ecommerce.test.mipaquete.com";
            $push = Push::where('platform', 'MiPaqueteTest')->first();
            
        } else {
            $url = "https://ecommerce.mipaquete.com";
            $push = Push::where('platform', 'MiPaquete')->first();
        }

        $data_string = json_encode($data);
        // $curl = curl_init("https://ecommerce.test.mipaquete.com" . $query);
        $curl = curl_init($url . $query);
        //dd($data);
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $push->object_id
        );
        curl_setopt($curl, CURLOPT_POST, true);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function authenticate($url) {
        $data = ['email' => env('MIPAQUETE_USER'), "password" => env('MIPAQUETE_PASS')];
        $data_string = json_encode($data);
        $curl = curl_init($url);
        //dd($data);
        $headers = array(
            'Content-Type: application/json',
        );
        curl_setopt($curl, CURLOPT_POST, true);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        if ($response['status'] == 200) {
            if($url == "https://ecommerce.mipaquete.com/api/auth"){
                Push::where('platform', 'MiPaquete')->update(['object_id' => $response['token'], 'updated_at' => date_add(date_create(), date_interval_create_from_date_string(date('Z') . " seconds"))]);
            } else {
                Push::where('platform', 'MiPaqueteTest')->update(['object_id' => $response['token'], 'updated_at' => date_add(date_create(), date_interval_create_from_date_string(date('Z') . " seconds"))]);
            }
            
        }
        return $response;
    }

    public function sendGet($query,$test) {
        if (false) {
            $url = "https://ecommerce.test.mipaquete.com";
            $push = Push::where('platform', 'MiPaqueteTest')->first();
        } else {
            $url = "https://ecommerce.mipaquete.com";
            $push = Push::where('platform', 'MiPaquete')->first();
            
        }
        $curl = curl_init($url . $query);
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $push->object_id
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function getCitiesAndRegions() {
        $response = $this->sendGet("/api/sendings/town",false);
        if ($response["status"] == 200) {
            $results = $response['result'];
            $towns = $results['towns'];
            foreach ($towns as $town) {
                if (strpos($town['name'], "BOGOT") !== false) {
                    $city = City::find(524);
                } else {
                    $city = City::where('name', 'like', $town['name'] . '%')->first();
                }
                if ($city) {
                    if ($city->attributes) {
                        $attributes = json_decode($city->attributes, true);
                        $attributes['MiPaquete'] = $town['_id'];
                        $city->attributes = json_encode($attributes);
                        $city->save();
                    } else {
                        $attributes = [];
                        $attributes['MiPaquete'] = $town['_id'];
                        $city->attributes = json_encode($attributes);
                        $city->save();
                    }
                } else {
                    echo $town['name'] . PHP_EOL;
                }
            }
        }
    }

    public function getEstimate(array $points) {
        //dd($points);
        foreach ($points as $value) {
            $result = $this->checkAddress($value['address']);
            if (!$result['result']) {
                dd($value['address']);
            }
        }
        $data['points'] = json_encode($points);
        $query = "api/bogota/estimate/";
        //dd($query);
        $response = $this->sendPost($data, $query,false);
        return $response;
    }

    public function getOrderShippingPrice(array $origin, array $destination, array $extras) {
        $data = $this->populateRequest($origin, $destination, $extras, false);
        $query = "/api/sendings/calculate";
        $admin = false;
        //dd($data);
        if($extras['user_id']<4){
            $admin = true;
        }
        $response = $this->sendPost($data, $query,$admin);
        if (array_key_exists('company', $response)) {
            $response['status'] = "success";
            $response['price'] = $response['company']['price'];
        } else {
            $response['status'] = "error";
            $response['message'] = "No pudimos obtener un precio";
        }
        return $response;
    }

    private function populateRequest(array $origin, array $destination, array $extras, bool $build) {
        //dd($points);
        $data = [];
        $data['type'] = 2;
        //        $data['width'] = 1;
//        $data['height'] = 1;
//        $data['large'] = 1;

        $data['quantity'] = 1;
        $data['payment_type'] = 1;
        $data['value_select'] = 1;



        if (array_key_exists('weight', $extras)) {
            $data['weight'] = $extras['weight'];
            if ($extras['weight'] > 5) {
                $data['type'] = 1;
                if (array_key_exists('width', $extras)) {
                    $data['width'] = $extras['width'];
                }
                if (array_key_exists('height', $extras)) {
                    $data['height'] = $extras['height'];
                }
                if (array_key_exists('large', $extras)) {
                    $data['large'] = $extras['large'];
                }
            }
        } else {
            $data['weight'] = 4;
        }
        if (array_key_exists('special_service', $extras)) {
            $data['special_service'] = $extras['special_service'];
        } else {
            $data['special_service'] = 0;
        }

        if (array_key_exists('value_collection', $extras)) {
            $data['value_collection'] = $extras['value_collection'];
        } else {
            $data['value_collection'] = 0;
        }

        if (array_key_exists('declared_value', $extras)) {
            if ($extras['declared_value']) {
                $data['declared_value'] = $extras['declared_value'];
            }
        } else {
            $data['declared_value'] = 30000;
        }

        $data['delivery'] = "5cb0f5fd244fe2796e65f9c";

        $origin_id = null;
        $destination_id = null;
        $originCity = City::find($origin['city_id']);
        if ($originCity) {
            $attributes = json_decode($originCity->attributes, true);
            if ($attributes) {
                if (array_key_exists('MiPaquete', $attributes)) {
                    $origin_id = $attributes['MiPaquete'];
                }
            }
        }

        $destinationCity = City::find($destination['city_id']);
        if ($destinationCity) {
            $attributes = json_decode($destinationCity->attributes, true);
            if ($attributes) {
                if (array_key_exists('MiPaquete', $attributes)) {
                    $destination_id = $attributes['MiPaquete'];
                }
            }
        }
        if ($origin_id && $destination_id) {
            $data['origin'] = $origin_id;
            $data['destiny'] = $destination_id;
        }
        if ($build) {
            $sender = [
                "name" => $origin['name'],
                "surname" => $origin['name'],
                "phone" => $origin['phone'],
                "cell_phone" => $origin['phone'],
                "collection_address" => $origin['address'] . " " . $origin['notes'],
            ];
            if (array_key_exists('merchant_email', $extras)) {
                $sender['email'] = $extras['merchant_email'];
            }
            if (array_key_exists('merchant_id', $extras)) {
                $sender['nit'] = $extras['merchant_id'];
            }
            $receiver = [
                "name" => $destination['name'],
                "surname" => $destination['name'],
                "phone" => $destination['phone'],
                "cell_phone" => $destination['phone'],
                "collection_address" => $destination['address'] . " " . $destination['notes'],
            ];
            if (array_key_exists('client_email', $extras)) {
                $receiver['email'] = $extras['client_email'];
            }
            $data['sender'] = $sender;
            $data['receiver'] = $receiver;
            $data['comments'] = $extras['description_delivery'];
        }
        return $data;
    }

    private function getUrl($user_id) {
        if ($user_id < 4) {
            return "/api/sendings-type";
        } else {
            return "/api/sendings-type";
        }
    }

    public function sendOrder(array $origin, array $destination, array $extras) {
        $data = $this->populateRequest($origin, $destination, $extras, true);
        $query = $this->getUrl($extras['user_id']);
        $admin = false;
        //dd($data);
        if($extras['user_id']<4){
            $admin = true;
        }
        $response = $this->sendPost($data, $query,$admin);
        if ($response['status'] == 200) {
            return ["status" => "success", "shipping_id" => $response['result']['sending']['_id']];
        }
        return ["status" => "error"];
    }

    public function checkAddress($address) {
        $data['address'] = $address;
        $query = env('RAPIGO_PROD') . "api/bogota/validate_address/";
        $response = $this->sendPost($data, $query,false);
        return $response;
    }

    public function checkStatus($key) {
        $data['key'] = $key;
        $query = env('RAPIGO_PROD') . "api/bogota/get_service_status/";
        $response = $this->sendPost($data, $query,false);
        return $response;
    }

    private function generateHash($id, $created_at) {
        return base64_encode(Hash::make($id . $created_at . env('LONCHIS_KEY')));
    }

    public function stopUpdate($data) {
        if ($data["extra_data"]["state"] == "realizada") {
            $this->stopComplete($data);
        } else if ($data["extra_data"]["state"] == "no_realizada") {
            $this->stopFailed($data);
        }
    }

    private function stopComplete($data) {
        $stop = Stop::where("code", $data["point"])->with("deliveries")->first();
        if ($stop) {
            foreach ($stop->deliveries as $delivery) {
                $delivery->status = "completed";
                $delivery->save();
            }
            $stop->status = "completed";
            $stop->save();
        }
    }

    private function stopFailed($data) {
        $stop = Stop::where("code", $data["point"])->with("deliveries.user")->first();
        if ($stop) {
            $stop->status = "completed";
            $stop->save();
            $route = $stop->route;
            $results = $this->checkStatus($route->code);
            $runnerName = $results["detalle"]["mensajero_asignado"];
            $runnerPhone = $results["detalle"]["mensajero_telefono"];
            $userAdmin = User::find(2);
            foreach ($stop->deliveries as $delivery) {
                $user = $delivery->user;
                $user->activationHash = $this->generateHash($user->id, $user->created_at);
                $delivery = Delivery::where("status", "pending")->where("user_id", $user->id)->orderBy('delivery', 'desc')->first();
                if ($delivery) {
                    $user->lunchHash = $user->activationHash;
                } else {
                    $user->lunchHash = null;
                }
                $delivery->status = "completed";
                $delivery->save();
            }
            Mail::to($userAdmin)->send(new StopFailed($stop, $runnerName, $runnerPhone));
        }
    }

    public function stopArrived($info) {
        $stop = Stop::where("code", $info["point"])->with("deliveries.user")->first();
        if ($stop) {
            $stop->status = "arrived";
            $stop->save();
            $followers = [];
            foreach ($stop->deliveries as $delivery) {
                array_push($followers, $delivery->user);
            }
            $payload = [];
            $data = [
                "trigger_id" => 1,
                "message" => "",
                "subject" => "",
                "object" => "Lonchis",
                "sign" => true,
                "payload" => $payload,
                "type" => "food_meal_arriving",
                "user_status" => "normal"
            ];
            $notifications = app('Notifications');
            $date = date("Y-m-d H:i:s");
            $notifications->sendMassMessage($data, $followers, null, true, $date, false);
        }

        return true;
    }

    public function routeCompleted($data) {
        $route = Route::where("provider_id", $data["key"])->where("provider", "Rapigo")->first();
        if ($route) {
            $route->status = "complete";
            $route->save();
        } else {
            return $data;
        }
    }

    public function routeStarted($data) {
        $route = Route::where("provider_id", $data["key"])->with("deliveries")->first();
        if ($route) {
            $route->status = "transit";
            $route->save();
            foreach ($route->deliveries as $delivery) {
                $delivery->status = "transit";
                $delivery->save();
            }
            return $route;
        } else {
            return $data;
        }
    }

    public function getActiveRoutesUpdate() {
        $routes = Route::where("status", "transit")->get();
        if (count($routes)) {
            foreach ($routes as $route) {
                $coverage = json_decode($route->coverage, true);
                $location = $coverage["location"];
                $status = $this->checkStatus($route->provider_id);
                if ($status) {
                    if (array_key_exists("detalle", $status)) {
                        $location["runner"] = $status["detalle"]["mensajero_asignado"];
                        $location["runner_phone"] = $status["detalle"]["mensajero_telefono"];
                        $location["lat"] = $status["detalle"]["latitud"];
                        $location["long"] = $status["detalle"]["longitud"];
                        $coverage["location"] = $location;
                        $route->coverage = json_encode($coverage);
                        $route->save();
                    }
                }
            }
        }
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
        if (true) {
            if (array_key_exists("type", $data)) {
                if ($data['type'] == "arrival") {
                    return $this->stopArrived($data);
                } else if ($data['type'] == "exit") {
                    return $this->stopUpdate($data);
                }
            }
            if (array_key_exists("message", $data)) {
                if ($data['message'] == "Mensajero Asignado") {
                    return $this->routeStarted($data);
                } else if ($data['message'] == "Servicio Finalizado") {
                    return $this->routeCompleted($data);
                }
            }
        }
        return $data;
    }

}
