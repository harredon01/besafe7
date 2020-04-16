<?php

namespace App\Services;

use App\Models\Stop;
use App\Models\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Delivery;
use Illuminate\Support\Facades\Mail;
use App\Mail\StopFailed;

class Rapigo {

    const ROUTE_HOUR_COST = 11000;
    const ROUTE_HOURS_EST = 3;

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
        foreach ($points as $value) {
            $result = $this->checkAddress($value['address']);
            if (!$result['result']) {
                dd($value['address']);
            }
        }
        $data['points'] = json_encode($points);
        $query = env('RAPIGO_PROD') . "api/bogota/estimate/";
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
        $query = env('RAPIGO_PROD') . "api/bogota/estimate/";
        //dd($query);
        $response = $this->sendPost($data, $query);
        $response['status'] = "success";
        return $response;
    }

    public function createRoute(array $points, $route, $stops) {
        $route->unit_cost = 20000;
        $data['type'] = 'hour';
        $deliveries = $route->deliveries;
        $date = date_create($deliveries[0]->delivery);
        $data['fecha_servicio'] = date_format($date, "m/d/Y");
        $data['hora_servicio'] = "08:45";
        foreach ($points as $value) {
            $result = $this->checkAddress($value['address']);
            if(!$result['result']){
                dd($value['address']);
            }
        }

        $data['points'] = json_encode($points);
        $query = env('RAPIGO_PROD') . "api/bogota/request_service/";
        //dd($data);
        $stopCodes = null;
        $serviceBookResponse = $this->sendPost($data, $query);
        if ($serviceBookResponse) {
            if (array_key_exists('paradas_referencia', $serviceBookResponse)) {
                $stopCodes = $serviceBookResponse['paradas_referencia'];
            }
        } else {
            $serviceBookResponse = [];
        }

        if ($stopCodes) {
            $route->provider_id = $serviceBookResponse['key'];
        }
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
            if($stopCodes){
                $stop->code = $stopCodes[$i]['ref_parada'];
            }
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
        $data['address'] = $address;
        $query = env('RAPIGO_PROD') . "api/bogota/validate_address/";
        $response = $this->sendPost($data, $query);
        return $response;
    }

    public function checkStatus($key) {
        $data['key'] = $key;
        $query = env('RAPIGO_PROD') . "api/bogota/get_service_status/";
        $response = $this->sendPost($data, $query);
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
