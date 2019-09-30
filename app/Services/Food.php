<?php

namespace App\Services;

use App\Models\User;
use App\Models\Article;
use App\Models\Delivery;
use App\Models\OrderAddress;
use App\Models\Route;
use App\Models\Stop;
use App\Models\Push;
use App\Jobs\RegenerateScenarios;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Address;
use App\Models\CoveragePolygon;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RouteDeliver;
use App\Mail\RouteOrganize;
use App\Mail\PurchaseOrder;
use App\Mail\Register;
use App\Mail\Newsletter1;
use App\Services\Rapigo;
use DB;
use Excel;

class Food {

    const OBJECT_ORDER = 'Order';
    const CREDIT_PRICE = 10000;
    const LUNCH_ROUTE = 15;
    const LUNCH_PROFIT = 1100;
    const ROUTE_HOUR_COST = 11000;
    const ROUTE_HOURS_EST = 3;
    const UNIT_LOYALTY_DISCOUNT = 11000;
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const PAYMENT_APPROVED = 'payment_approved';
    const PAYMENT_DENIED = 'payment_denied';
    const PLATFORM_NAME = 'food';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';

    public function suspendDelivery(User $user, $option) {
        $className = "App\\Services\\Notifications";
        $platFormService = new $className();
        $payload = [];
        $date = date("Y-m-d H:i:s");
        $followers = [$user];
        if ($option == "cancel") {
            Delivery::where("user_id", $user->id)->where("status", "pending")->update(['status' => 'suspended']);
            Delivery::where("user_id", $user->id)->where("status", "deposit")->delete();
            $push = $user->push()->where("platform", "food")->first();
            $push->credits = 0;
            $push->save();
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "subject" => "",
                "object" => "Lonchis",
                "sign" => true,
                "payload" => $payload,
                "type" => "food_meal_suspended",
                "user_status" => "normal"
            ];
            $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
        } else {
            $delivery = Delivery::where("user_id", $user->id)->where("status", "pending")->orderBy('id', 'desc')->first();
            if ($delivery) {
                $delivery->delete();
                $data = [
                    "trigger_id" => $user->id,
                    "message" => "",
                    "subject" => "",
                    "object" => "Lonchis",
                    "sign" => true,
                    "payload" => $payload,
                    "type" => "food_meal_traded",
                    "user_status" => "normal"
                ];
                $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
            } else {
                $deposit = Delivery::where("user_id", $user->id)->where("status", "deposit")->orderBy('id', 'desc')->first();
                if ($deposit) {
                    $deposit->delete();
                    $push = $user->push()->where("platform", "food")->first();
                    $push->credits = 0;
                    $push->save();
                    $data = [
                        "trigger_id" => $user->id,
                        "message" => "",
                        "subject" => "",
                        "object" => "Lonchis",
                        "sign" => true,
                        "payload" => $payload,
                        "type" => "food_meal_suspended",
                        "user_status" => "normal"
                    ];
                    $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
                } else {
                    $this->suspendDelivery($user, "cancel");
                }
            }
        }
    }

    public function inviteUser(User $user) {
        $className = "App\\Services\\Notifications";
        $platFormService = new $className();
        $deliveryObj = Delivery::find(8850);
        $delivery = [
            "delivery" => $deliveryObj
        ];
        $payload = [
            "page" => "DeliveryProgramPage",
            "page_payload" => $delivery
        ];
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "prueba mensaje programacion",
            "subject" => "prueba mensaje programacion",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "program_reminder",
            "user_status" => "normal"
        ];
        $date = date_create($deliveryObj->delivery);
        $date = date_format($date, "Y-m-d");
        $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
    }

    public function loadDayConfig($deliveryDate) {
        //$date = date("Y-m-d",$deliveryDate);
        $date = date_create($deliveryDate);
        $dateUse = date_format($date, "Y-m-d");
//        $deliveries = Delivery::where('delivery', $date)->get();
        //$articles = Article::where('start_date',$date)->get();
        $articles = Article::where('start_date', $dateUse)->get();
        $father = [];
        //$keywords = ['fruit', 'soup', 'meat', 'chicken'];
        $keywords = ['fruit', 'soup'];
        foreach ($articles as $article) {

            $attributes = json_decode($article->attributes, true);
            $entradas = [];
            $entradasNombre = [];
            foreach ($attributes['entradas'] as $value) {
                $entradas[$value['codigo']] = 0;
                $entradasNombre[$value['codigo']] = $value['valor'];
            }
            $main = [];
            $mainNombre = [];
            foreach ($attributes['plato'] as $item) {
                $main[$item['codigo']] = 0;
                $mainNombre[$item['codigo']] = $item['valor'];
            }
            foreach ($keywords as $keyword) {
                $father['keywords'][$keyword] = 0;
            }
//            array_push($plate, [0 => $main]);
//            array_push($plate, [0 => $entradas]);
            $father[$article->id]['count'] = 0;
            $father[$article->id]['starter'] = $entradas;
            $father[$article->id]['main'] = $main;
            $father[$article->id]['name'] = $article->name;
            $father[$article->id]['starter_name'] = $entradasNombre;
            $father[$article->id]['main_name'] = $mainNombre;
        }
        return array("father" => $father, "articles" => $articles, "keywords" => $keywords);
    }

    public function countConfigElements($deliveries, $config) {
        $father = $config['father'];
        $keywords = $config['keywords'];
        foreach ($deliveries as $value) {
            $details = json_decode($value->details, true);
            if (array_key_exists("dish", $details)) {
                $dish = $details["dish"];
                $father[$dish['type_id']]['count'] ++;
                if (array_key_exists('starter_id', $dish)) {
                    if ($dish['starter_id']) {
                        $father[$dish['type_id']]['starter'][$dish['starter_id']] ++;
                    }
                }
                $father[$dish['type_id']]['main'][$dish['main_id']] ++;
                foreach ($keywords as $word) {
                    if (array_key_exists('starter_id', $dish)) {
                        if (strpos($dish['starter_id'], $word) !== false) {
                            $father["keywords"][$word] ++;
                        }
                    }
                    if (strpos($dish['main_id'], $word) !== false) {
                        $father["keywords"][$word] ++;
                    }
                }
            }
        }
        return $father;
    }

    public function printTotalsConfig($config) {
        $articles = $config['articles'];
        $father = $config['father'];
        $keywords = $config['keywords'];
        $finalresult = [];
        $finalresult['keywords'] = [];
        $finalresult['totals'] = [];
        $finalresult['dish'] = [];
        $finalresult['excel'] = [];
        foreach ($keywords as $keyword2) {
            if ($father['keywords'][$keyword2] > 0) {
                $keyPrint = "Total " . $keyword2 . ": " . $father['keywords'][$keyword2];
                array_push($finalresult['keywords'], $keyPrint);
            }
        }
        foreach ($articles as $art) {
            $attributes = json_decode($art->attributes, true);
            if ($father[$art->id]['count'] > 0) {
                $title = $father[$art->id]['name'] . ": " . $father[$art->id]['count'] . " ";
                array_push($finalresult['totals'], $title);
                $titulo = "### " . $father[$art->id]['name'] . ": " . $father[$art->id]['count'] . "  ";
                $elTipo = ["Tipo", $father[$art->id]['name'], $father[$art->id]['count']
                ];
                array_push($finalresult['excel'], $father[$art->id]['name']);
                array_push($finalresult['dish'], $titulo);
            }

            foreach ($attributes['entradas'] as $art2) {
                if ($father[$art->id]['starter'][$art2['codigo']] > 0) {
                    $entrada = $father[$art->id]['starter_name'][$art2['codigo']] . ": " . $father[$art->id]['starter'][$art2['codigo']] . "  ";
                    array_push($finalresult['dish'], $entrada);
                    $elTipo = ["Entrada", $father[$art->id]['starter_name'][$art2['codigo']], $father[$art->id]['starter'][$art2['codigo']]];
                    array_push($finalresult['excel'], $father[$art->id]['starter_name'][$art2['codigo']]);
                }
            }
            foreach ($attributes['plato'] as $art3) {
                if ($father[$art->id]['main'][$art3['codigo']] > 0) {
                    $fuerte = $father[$art->id]['main_name'][$art3['codigo']] . ": " . $father[$art->id]['main'][$art3['codigo']] . "  ";
                    array_push($finalresult['dish'], $fuerte);
                    $elTipo = ["Plato", $father[$art->id]['main_name'][$art3['codigo']], $father[$art->id]['main'][$art3['codigo']]];
                    array_push($finalresult['excel'], $father[$art->id]['main_name'][$art3['codigo']]);
                }
            }
        }

        return $finalresult;
    }

    public function getPurchaseOrder($deliveries) {
        if (count($deliveries) > 0) {
            $dayConfig = $this->loadDayConfig($deliveries[0]->delivery);
//        $articles = $dayConfig['articles'];
//        $father = $dayConfig['father'];
//        $keywords = $dayConfig['keywords'];
            //dd($father);
            $facturas = [];
            foreach ($deliveries as $delivery) {
                $details = json_decode($delivery->details, true);
                if (array_key_exists("factura", $details)) {
                    if (array_key_exists("order_id", $details)) {
                        array_push($facturas, $details['order_id']);
                    }
                }
            }
            $path = "";
            if (count($facturas) > 0) {
                $className = "App\\Services\\StoreExport";
                $billingService = new $className();
                $path = $billingService->dailyInvoices($facturas);
            }

            $dayConfig['father'] = $this->countConfigElements($deliveries, $dayConfig);
            //dd($father);
            $dayConfig['totals'] = $this->printTotalsConfig($dayConfig);
            $users = User::whereIn('id', [2, 77])->get();
            //dd($path);
            Mail::to($users)->send(new PurchaseOrder($dayConfig, $path));
            return $dayConfig;
        }
    }

    public function sendReminder() {
        $date = date_create();
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        if ($dayofweek == 0) {
            return true;
        }
        $type = "program_reminder";
        $date = $this->getNextValidDate($date);
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        if ($dayofweek == 1) {
            $type = "program_reminder2";
        }
        $tomorrow = date_format($date, "Y-m-d");
        $deliveries = Delivery::where("status", "pending")->with(['user'])->where("delivery", "<", $tomorrow . " 23:59:59")->where("delivery", ">", $tomorrow . " 00:00:00")->get();
        if (count($deliveries) > 0) {
            $followers = [];
            $className = "App\\Services\\Notifications";
            $platFormService = new $className();
            foreach ($deliveries as $deliveryObj) {

                $delivery = [
                    "delivery" => $deliveryObj
                ];
                $payload = [
                    "page" => "DeliveryProgramPage",
                    "page_payload" => $delivery
                ];
                $followers = [$deliveryObj->user];
                $data = [
                    "trigger_id" => -1,
                    "message" => "",
                    "subject" => "Ya escogiste tu almuerzo de mañana?",
                    "object" => "Lonchis",
                    "sign" => true,
                    "payload" => $payload,
                    "type" => $type,
                    "user_status" => "normal"
                ];
                $date = date_create($deliveryObj->delivery);
                $date = date_format($date, "Y-m-d");
                $platFormService->sendMassMessage($data, $followers, null, true, $date, false);
            }
        }
    }

    public function sendNewsletter() {
        $date = date_create();
        $type = "program_reminder";
        $tomorrow = date_format($date, "Y-m-d");
        // id > 130 and id < 200 bota 500
        $followers = DB::select("select id,email from users where optinMarketing = 1");
        if (count($followers) > 0) {
            $payload = [
            ];

            $data = [
                "trigger_id" => -1,
                "message" => "",
                "subject" => "Visita tu correo para enterarte de nuestros menus de esta semana",
                "object" => "Lonchis",
                "sign" => true,
                "payload" => $payload,
                "type" => 'newsletter_food',
                "user_status" => "normal"
            ];
            $date = date_create();
            $date = date_format($date, "Y-m-d");

            $className = "App\\Services\\Notifications";
            $platFormService = new $className();
            $platFormService->sendMassMessage($data, $followers, null, true, $date, false);
            foreach ($followers as $user) {
                Mail::to($user->email)->send(new Newsletter1());
            }
        }
    }


    public function checkScenario($results, $hash) {
        if (count($results) > 0) {
            $scenroute = $results[0];
            return $this->checkHash($scenroute->id, $scenroute->created_at, $hash);
        }
        return false;
    }
   
    public function getStopDetails($results,$stop,$config) {
        $stopDescription = "";
        $address = $stop->address;
        $phone = "";
        foreach ($stop->deliveries as $stopDel) {
            $delUser = $stopDel->user;
            $phone = $delUser->cellphone;
            $arrayDel = [$stop->id, $address->address . " " . $address->notes, $stopDel->id, $delUser->firstName . " " . $delUser->lastName, $delUser->cellphone];
            $descr = $delUser->firstName . " " . $delUser->lastName . " ";
            $details = json_decode($stopDel->details, true);
            if (array_key_exists("deliver", $details)) {
                if ($details['deliver'] == "envase") {
                    $descr = $descr . "Envase Retornable,  ";
                    array_push($arrayDel, "Retornable");
                }
            } else {
                array_push($arrayDel, "Desechable");
            }
            if (array_key_exists("pickup", $details)) {
                if ($details['pickup'] == "envase") {
                    $descr = $descr . "Recoger envase,  ";
                    array_push($arrayDel, "SI");
                }
            } else {
                array_push($arrayDel, "NO");
            }

            if (array_key_exists("factura", $details)) {
                $descr = $descr . "Enviar factura,  ";
                array_push($arrayDel, "SI");
            } else {
                array_push($arrayDel, "NO");
            }
            $descr = $descr . "Entregar id: " . $stopDel->id . ".  ";
            $stopDel->region_name = $descr;
            $delConfig = $config;
            $dels = [$stopDel];
            $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
            $delTotals = $this->printTotalsConfig($delConfig);

            $arrayDel = array_merge($arrayDel, $delTotals['excel']);
            array_push($arrayDel, $stopDel->observation);

            array_push($results, $arrayDel);
            $stopDescription = $stopDescription . $descr;
        }

        if ($stop->stop_order == 1) {
            $stopDescription = "Recoger los almuerzos de la ruta: " . $stop->route_id;
            //array_push($arrayStop, $stopDescription);
        }
        if ($stop->stop_order == 3) {
            $stopDescription = "Entregar los envases de la ruta: " . $stop->route_id;
            //array_push($arrayStop, $stopDescription);
        }
        return ["results" => $results, "description" => $stopDescription,"phone" => $phone];
    }


    public function writeFile($data, $title) {
        return Excel::create($title, function($excel) use($data, $title) {

                    $excel->setTitle($title);
                    // Chain the setters
                    $excel->setCreator('Hoovert Arredondo')
                            ->setCompany('Lonchis');
                    // Call them separately
                    $excel->setDescription('This report is clasified');
                    foreach ($data as $page) {
//                foreach ($page["rows"] as $key => $value) {
//                    if ($page["rows"][$key]) {
//                        if (array_key_exists("labels", $page["rows"][$key])) {
//                            unset($page["rows"][$key]["labels"]);
//                        }
//                        if (array_key_exists("projects", $page["rows"][$key])) {
//                            unset($page["rows"][$key]["projects"]);
//                        }
//                        if (array_key_exists("people", $page["rows"][$key])) {
//                            unset($page["rows"][$key]["people"]);
//                        }
//                    }
//                }
                        $excel->sheet(substr($page["name"], 0, 30), function($sheet) use($page) {
                            $sheet->fromArray($page["rows"], null, 'A1', true);
                        });
                    }
                })->store('xlsx', storage_path('app/exports'));
    }

    public function generateHash($id, $created_at) {
        return base64_encode(Hash::make($id . $created_at . env('LONCHIS_KEY')));
    }

    public function checkHash($id, $created_at, $hash) {
        $keyDecoded = base64_decode($hash);
        if (Hash::check($id . $created_at . env('LONCHIS_KEY'), $keyDecoded)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function turnDeliveriesIntoStops($deliveries, $preorganize) {
        $stops = array();

        if (count($deliveries) > 0) {
            $initialAddress = $deliveries[0]->address;
            $initialAddressId = $deliveries[0]->address_id;
            $deliveryCounter = 0;
            $deliveryShipping = 0;
            $packages = [];
            $pickups = [];
            $totalCounter = 0;
            foreach ($deliveries as $delivery) {
                $createNew = false;
                $attributes = json_decode($delivery->details, true);
                if ($preorganize) {
                    $totalCounter++;
                    if ($delivery->address == $initialAddress) {
                        if (($delivery->provider == "Rapigo" && $deliveryCounter < 15) || ($delivery->provider == "Basilikum" && $deliveryCounter < 80)) {
                            $deliveryCounter++;
                            $deliveryShipping += $delivery->shipping;
                            array_push($packages, $delivery);
                            if (array_key_exists("pickup", $attributes)) {
                                if ($attributes['pickup'] == 'envase') {
                                    $delUser = User::find($delivery->user_id);
                                    $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                                    array_push($pickups, $desc);
                                }
                            }
                        } else {
                            $createNew = true;
                        }
                    } else {
                        $createNew = true;
                    }
                    if ($createNew) {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddressId,
                            "latitude" => $delivery->lat,
                            "longitude" => $delivery->long,
                            "shipping" => $deliveryShipping,
                            "deliveries" => $packages,
                            "pickups" => $pickups
                        ];
                        array_push($stops, $stop);
                        $packages = [];
                        array_push($packages, $delivery);
                        $pickups = [];
                        if (array_key_exists("pickup", $attributes)) {
                            if ($attributes['pickup'] == 'envase') {
                                $delUser = User::find($delivery->user_id);
                                $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                                array_push($pickups, $desc);
                            }
                        }
                        $deliveryCounter = 1;
                        $deliveryShipping = $delivery->shipping;
                        $initialAddress = $delivery->address;
                        $initialAddressId = $delivery->address_id;
                    }
                    if ($totalCounter == count($deliveries)) {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddressId,
                            "latitude" => $delivery->lat,
                            "longitude" => $delivery->long,
                            "shipping" => $deliveryShipping,
                            "deliveries" => $packages,
                            "pickups" => $pickups
                        ];
                        array_push($stops, $stop);
                    }
                } else {
                    if (array_key_exists("pickup", $attributes)) {
                        if ($attributes['pickup'] == 'envase') {
                            $delUser = User::find($delivery->user_id);
                            $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                            array_push($pickups, $desc);
                        }
                    }
                    $packages = [$delivery];
                    $stop = [
                        "amount" => 1,
                        "address_id" => $delivery->address_id,
                        "latitude" => $delivery->lat,
                        "longitude" => $delivery->long,
                        "shipping" => $delivery->shipping,
                        "deliveries" => $packages,
                        "pickups" => $pickups
                    ];
                    array_push($stops, $stop);
                }
            }
            //dd($stops);
            if ($preorganize) {
                //usort($stops, array($this, 'cmp'));
            }
        }
        return $stops;
    }

    public function runRecurringTask() {
        $user = User::find(2);
        $polygons = CoveragePolygon::where('merchant_id', 1299)->where("provider", "Rapigo")->get();
        $this->prepareRoutingSimulation($polygons);
        $polygons = CoveragePolygon::where('merchant_id', 1299)->where("provider", "Basilikum")->get();
        $this->prepareRoutingSimulation($polygons, "Basilikum");
        $results = $this->getShippingCosts($user, "pending");
        $deliveries = Delivery::where("status", "transit")->get();
        $this->getPurchaseOrder($deliveries);
    }

    public function runInstructions() {
        $user = User::find(2);
        $routes = Route::where("status", "built")->with(['deliveries.user'])->get();
        $results = $this->buildScenario($routes);
        Mail::to($user)->send(new RouteOrganize($results));
        Mail::to($user)->send(new RouteDeliver($results));
    }

    public function getRouteInfo($delivery_id) {
        $theData = ["delivery" => $delivery_id];
        $routes = DB::select(" select route_id from delivery_route where delivery_id = :delivery", $theData);
        if (count($routes) > 0) {
            $route = Route::where("id", $routes[0]->route_id)->with(['stops.address'])->first();
            return $route;
        }
        return null;
    }

    public function getNextValidDate($date) {
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        if ($dayofweek > 0 && $dayofweek < 5) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        } else if ($dayofweek == 5) {
            date_add($date, date_interval_create_from_date_string("3 days"));
        } else if ($dayofweek == 6) {
            date_add($date, date_interval_create_from_date_string("2 days"));
        } else if ($dayofweek == 0) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        }

        $isHoliday = $this->checkIsHoliday($date);

        while ($isHoliday) {
            $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
            if ($dayofweek == 5) {
                date_add($date, date_interval_create_from_date_string("3 days"));
            } else if ($dayofweek == 6) {
                date_add($date, date_interval_create_from_date_string("2 days"));
            } else {
                date_add($date, date_interval_create_from_date_string("1 days"));
            }
            $isHoliday = $this->checkIsHoliday($date);
        }
        return $date;
    }

    public function checkIsHoliday($date) {
        $holidays = [
            "2019-06-03",
            "2019-06-24",
            "2019-07-01",
            "2019-08-07",
            "2019-08-19",
            "2019-10-14",
            "2019-11-04",
            "2019-12-24",
            "2019-12-25",
            "2019-12-26",
            "2019-12-27",
            "2019-12-30",
            "2019-12-31",
            "2020-01-01",
            "2020-01-02",
            "2020-01-03",
            "2020-01-06",
        ];
        foreach ($holidays as $day) {
            if ($day == date_format($date, "Y-m-d")) {
                return true;
            }
        }
        return false;
    }

    public function reprogramDeliveries() {
        $date = date_create();
//        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
//        dd($dayofweek);
//        if ($dayofweek < 5 && $dayofweek > 0) {
//            date_add($date, date_interval_create_from_date_string("1 days"));
//        } else if ($dayofweek == 5) {
//            date_add($date, date_interval_create_from_date_string("3 days"));
//        } else {
//            return null;
//        }
        $la = date_format($date, "Y-m-d");
//        $date = date_create($la . " 23:59:59");
//        dd($date);
        $deliveries = Delivery::whereIn('status', ['pending', 'deposit'])->where('delivery', '<', $la . " 23:59:59")->orderBy('delivery', 'desc')->get();
        foreach ($deliveries as $item) {
            $delivery = Delivery::where('id', "<>", $item->id)->where('user_id', $item->user_id)->where('delivery', '>', $item->delivery)->orderBy('delivery', 'desc')->first();
            $delivery2 = null;
            if ($delivery) {
                $date = date_create($delivery->delivery);
                $delivery2 = Delivery::where('user_id', $item->user_id)->where('delivery', '>', $item->delivery)->orderBy('delivery', 'asc')->first();
                if ($delivery->id == $delivery2->id) {
                    $delivery2 = null;
                }
            } else {
                $date = date_create();
            }
            $date = $this->getNextValidDate($date);
            if ($delivery) {
                if ($delivery->status == "deposit") {
                    $item->delivery = $delivery->delivery;
                    $delivery->delivery = date_format($date, "Y-m-d") . " 12:00:00";
                    $delivery->save();
                } else {
                    $item->delivery = date_format($date, "Y-m-d") . " 12:00:00"; 
                }
                if ($delivery2) {
                    $tempAttrs = $delivery2->details;
                    $delivery2->details = $item->details;
                    $delivery2->save();
                    $item->details = $tempAttrs;
                }
            } else {
                $item->delivery = date_format($date, "Y-m-d") . " 12:00:00";
            }
            $item->save();
        }
    }

}
