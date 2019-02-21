<?php

namespace App\Services;

use App\Models\Stop;
use App\Models\Route;
use App\Models\Delivery;
use Illuminate\Support\Facades\Mail;
use App\Mail\StopFailed;

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

    public function createRoute(array $points, $type, $route,$stops) {
        if ($type == "hour") {
            $data['type'] = 'hour';
        }
        $data['points'] = json_encode($points);
        $query = env('RAPIGO_TEST') . "api/bogota/request_service/";
        $serviceBookResponse = $this->sendPost($data, $query);
        $stopCodes = $serviceBookResponse['points'];
        $route->code = $serviceBookResponse['key'];
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
            $stop->code = $stopCodes[$i];
            $stop->status = "pending";
            $stop->save();
            $i++;
        }
        $route->save();
        $route->stops = $stops;
        return $route;
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

    private function generateHash($id, $created_at, $updated_at) {
        return base64_encode(Hash::make($id . $created_at . $updated_at . env('LONCHIS_KEY')));
    }

    public function stopUpdate($data) {
        if ($data["extra_data"]["state"] == "realizada") {
            $this->stopComplete($data);
        } else if ($data["extra_data"]["state"] == "no_realizada") {
            $this->stopFailed($data);
        }
    }

    private function stopComplete($data) {
        $stop = Stop::where("code", $data["key"])->with("deliveries")->first();
        foreach ($stop->deliveries as $delivery) {
            $delivery->status = "completed";
            $delivery->save();
        }
        $stop->status = "completed";
        $stop->save();
    }

    private function stopFailed($data) {
        $stop = Stop::where("code", $data["key"])->with("deliveries.user")->first();
        $route = $stop->route;
        $results = $this->checkStatus($route->code);
        $runnerName = $results["detalle"]["mensajero_asignado"];
        $runnerPhone = $results["detalle"]["mensajero_telefono"];
        $userAdmin = User::find(2);
        foreach ($stop->deliveries as $delivery) {
            $user = $delivery->user;
            $user->activationHash = $this->generateHash($user->id, $user->created_at, $user->updated_at);
            $delivery = Delivery::where("status", "pending")->where("user_id", $user->id)->orderBy('delivery', 'desc')->first();
            if ($delivery) {
                $user->lunchHash = $user->activationHash;
            } else {
                $user->lunchHash = null;
            }
        }
        Mail::to($userAdmin)->send(new StopFailed($stop, $runnerName, $runnerPhone));
    }

    public function stopArrived($info) {
        $stop = Stop::where("code", $info["key"])->with("deliveries.user")->first();
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
        $className = "App\\Services\\EditAlerts";
        $editAlerts = new $className;
        $date = date("Y-m-d H:i:s");
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);
        return true;
    }

    public function routeCompleted($data) {
        $route = Route::where("code", $data["key"])->first();
        $route->status = "complete";
        $route->save();
    }

    public function routeStarted($data) {
        $route = Route::where("code", $data["key"])->first();
        $route->status = "transit";
        $route->save();
    }

    public function getActiveRoutesUpdate() {
        $routes = Route::where("status", "transit")->get();
        if (count($routes)) {
            foreach ($routes as $route) {
                $route->coverage = json_parse($route->coverage, true);
                $location = $route->coverage["location"];
                $status = $this->checkStatus($route->code);
                $location["runner"] = $status["detalle"]["mensajero_asignado"];
                $location["runner_phone"] = $status["detalle"]["mensajero_telefono"];
                $location["lat"] = $status["detalle"]["latitud"];
                $location["long"] = $status["detalle"]["longitud"];
                $route->coverage["location"] = $location;
                $route->coverage = json_encode($route->coverage);
                $route->save();
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
            if ($data['codigo'] == "parada_llegue") {
                $this->stopArrived($data);
            } else if ($data['codigo'] == "parada_finalizada") {
                $this->stopUpdate($data);
            } else if ($data['codigo'] == "parada_finalizada") {
                $this->stopUpdate($data);
            } else if ($data['codigo'] == "servicio_asignado") {
                $this->routeStarted($data);
            } else if ($data['codigo'] == "servicio_finalizado") {
                $this->routeCompleted($data);
            }
        }
    }

}
