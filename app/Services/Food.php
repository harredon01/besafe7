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
use App\Mail\NewsletterMenus;
use App\Jobs\SendMessage;
use App\Mail\Newsletter4;
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
        $platFormService = app('Notifications');
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
        $platFormService = app('Notifications');
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
    
    private function getSubject($tomorrow){
        $articles = Article::where("start_date",$tomorrow . " 00:00:00")->get();
    }

    public function sendReminder() {
        $date = date_create();
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        $type = "program_reminder2";
        $date = $this->getNextValidDate($date);
        $tomorrow = date_format($date, "Y-m-d");
        $articles = Article::where('start_date',$tomorrow . " 00:00:00")->get();
        $remindMessage = null;
        $sendToAll = false;
        foreach ($articles as $art) {
            if($art->pagetitle){
                $remindMessage = $art->pagetitle;
            }
            if($art->metadescription){
                $sendToAll = true;
            }
        }
        //$deliveries = Delivery::where("status", "pending")->with(['user'])->where("delivery", "<", $tomorrow . " 23:59:59")->where("delivery", ">", $tomorrow . " 00:00:00")->get();
        $thedata = ["tom1"=>$tomorrow . " 00:00:00","tom2"=>$tomorrow . " 23:59:59"];
        $deliveries = null;
        if($sendToAll){
            $deliveries = DB::select("SELECT id as user_id FROM users where optinMarketing = 1;");
        } else {
            $deliveries = DB::select(""
                        . "SELECT * FROM food.deliveries WHERE status = 'pending' AND user_id NOT IN "
            . " (SELECT DISTINCT user_id FROM food.deliveries WHERE status = 'enqueue' AND delivery BETWEEN :tom1 AND :tom2) GROUP BY user_id;"
                        . "", $thedata);
        }
        if(!$remindMessage){
            $remindMessage = "¿Ya sabes que vas a almorzar mañana?";
        }
        if (count($deliveries) > 0) {
            $followers = [];
            $platFormService = app('Notifications');
            foreach ($deliveries as $deliveryObj) {

                $delivery = [
                    "delivery" => $deliveryObj
                ];
                $payload = [
                    "page" => "DeliveryProgramPage",
                    "page_payload" => $delivery
                ];
                
                $dauser = [
                    "id"=> $deliveryObj->user_id
                ];
                $followers = [json_decode(json_encode($dauser))];
                $data = [
                    "trigger_id" => -1,
                    "message" => "",
                    "subject" => $remindMessage,
                    "object" => "Lonchis",
                    "sign" => true,
                    "payload" => $payload,
                    "type" => $type,
                    "user_status" => "normal"
                ];
                if($sendToAll){
                    $date = date_create($tomorrow . " 00:00:00");
                } else {
                    $date = date_create($deliveryObj->delivery);
                }
                
                $date = date_format($date, "Y-m-d");
                $platFormService->sendMassMessage($data, $followers, null, true, $date, false);
            }
        }
    }

    public function getDataNewsletter() {
        $start_date = "2020-10-19 00:00:00";
        $end_date = "2020-10-24 23:59:59";
        $articles = Article::whereBetween('start_date', [$start_date, $end_date])->orderBy('start_date', 'asc')->get();
        $days = [];
        for ($x = 0; $x < 6; $x++) {
            $day = [
                "imagen" => "",
                "completo_imagen" => "",
                "light_imagen" => "",
                "vegetariano_imagen" => "",
                "titulo" => "",
                "vegetariano_t" => "",
                "light_t" => "",
                "completo_t" => "",
                "vegetariano_d" => "",
                "light_d" => "",
                "completo_d" => "",
                "vegetariano_et" => "",
                "light_et" => "",
                "completo_et" => "",
                "et1" => "",
                "et2" => "",
                "ed1" => "",
                "ed2" => "",
            ];
            array_push($days, $day);
        }
        foreach ($articles as $article) {
            $date = date_create($article->start_date);
            $attributes = json_decode($article->attributes, true);
            $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
            $dateMonth = date('d', strtotime(date_format($date, "Y-m-d H:i:s")));
            $days[($dayofweek - 1)]["titulo"] = $dateMonth;
            if (!$days[($dayofweek - 1)]["imagen"]) {
                $days[($dayofweek - 1)]["imagen"] = $attributes["plato"][0]["imagen"];
            }
            if (!$days[($dayofweek - 1)][strtolower($article->name) ."_imagen"]) {
                $days[($dayofweek - 1)][strtolower($article->name) ."_imagen"] = $attributes["plato"][0]["imagen"];
            }
            if (!$days[($dayofweek - 1)][strtolower($article->name) . "_t"]) {
                $days[($dayofweek - 1)][strtolower($article->name) . "_t"] = $attributes["plato"][0]["valor"];
            }
            if (count($attributes["entradas"]) > 0) { 
                if (!$days[($dayofweek - 1)][strtolower($article->name) . "_et"]) {
                    $days[($dayofweek - 1)][strtolower($article->name) . "_et"] = $attributes["entradas"][0]["valor"];
                }
                if (count($attributes["entradas"]) > 1) { 
                    $days[($dayofweek - 1)]["et1"] = $attributes["entradas"][0]["valor"];
                    $days[($dayofweek - 1)]["et2"] = $attributes["entradas"][1]["valor"];
                    $days[($dayofweek - 1)]["ed1"] = $attributes["entradas"][0]["descripcion"];
                    $days[($dayofweek - 1)]["ed2"] = $attributes["entradas"][1]["descripcion"];
                }
            }
            if (!$days[($dayofweek - 1)][strtolower($article->name) . "_d"]) {
                $days[($dayofweek - 1)][strtolower($article->name) . "_d"] = $attributes["plato"][0]["descripcion"];
            }
        }
        //dd($days);
        return $days;
    }

    public function sendNewsletter() {
        $days = $this->getDataNewsletter();

        $date = date_create();
        // id > 130 and id < 200 bota 500
        //$followers = DB::select('select user_id as id from deliveries where delivery ="2020-07-17 12:00:00" and status in ("scheduled","completed")');
        //dd($followers);
        $followers = DB::select("select id,email from users where optinMarketing = 1");
        if (count($followers) > 0) {
            $payload = [
            ];

            $data = [
                "trigger_id" => -1,
                "message" => "",
                "subject" => "Visita tu correo para enterarte de nuestros menus de la semana",
                "object" => "Lonchis",
                "sign" => true,
                "payload" => $payload,
                "type" => 'newsletter_food',
                "user_status" => "normal" 
            ];
            $date = date_create();
            $date = date_format($date, "Y-m-d");

            $platFormService = app('Notifications');
            //$platFormService->sendMassMessage($data, $followers, null, true, $date, false);
            foreach ($followers as $user) { 
                Mail::to($user->email)->send(new NewsletterMenus($days,"Octubre","Octubre"));
                //Mail::to($user->email)->send(new Newsletter4());
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

    public function getStopDetails($results, $stop, $config) {
        $stopDescription = "";
        $address = $stop->address;
        $phone = "";
        foreach ($stop->deliveries as $stopDel) {
            $delUser = $stopDel->user;
            $phone = $delUser->cellphone;
            $arrayDel = [$stop->id, $address->address . " " . $address->notes,$address->name,$address->phone, $stopDel->id, $delUser->firstName . " " . $delUser->lastName, $delUser->cellphone];
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
        return ["results" => $results, "description" => $stopDescription, "phone" => $phone];
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
    public function updateDeliveries() {
        Delivery::where('status','scheduled')->update(['status'=>'completed']);
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
                        if (($delivery->provider == "Rapigo" && $deliveryCounter < 15) || ($delivery->provider == "Basilikum" && $deliveryCounter < 18)) {
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
        if ($dayofweek > 0 && $dayofweek < 6) {
            date_add($date, date_interval_create_from_date_string("1 days"));
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
            "2020-01-01",
            "2020-01-02",
            "2020-01-03",
            "2020-01-06",
            "2020-03-23",
            "2020-04-09",
            "2020-04-10",
            "2020-05-25",
            "2020-06-15",
            "2020-06-22",
            "2020-06-29",
            "2020-07-20",
            "2020-08-07",
            "2020-08-17",
            "2020-10-12",
            "2020-11-02",
            "2020-11-16",
            "2020-12-08",
            "2020-12-24",
            "2020-12-25",
            "2020-12-31",
        ];
        ;
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
