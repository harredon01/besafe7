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
use App\Mail\ScenarioSelect;
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
        $className = "App\\Services\\EditAlerts";
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
        $className = "App\\Services\\EditAlerts";
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

    public function updateUserDeliveriesAddress($user_id, $address_id) {
        $address = OrderAddress::find($address_id);
        if ($address) {
            $deliveries = Delivery::where('user_id', $user_id)->update(['address_id' => $address_id]);
            return $deliveries;
        }
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
                array_push($finalresult['dish'], $titulo);
            }

            foreach ($attributes['entradas'] as $art2) {
                if ($father[$art->id]['starter'][$art2['codigo']] > 0) {
                    $entrada = $father[$art->id]['starter_name'][$art2['codigo']] . ": " . $father[$art->id]['starter'][$art2['codigo']] . "  ";
                    array_push($finalresult['dish'], $entrada);
                }
            }
            foreach ($attributes['plato'] as $art3) {
                if ($father[$art->id]['main'][$art3['codigo']] > 0) {
                    $fuerte = $father[$art->id]['main_name'][$art3['codigo']] . ": " . $father[$art->id]['main'][$art3['codigo']] . "  ";
                    array_push($finalresult['dish'], $fuerte);
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
            $dayConfig['father'] = $this->countConfigElements($deliveries, $dayConfig);
            //dd($father);
            $dayConfig['totals'] = $this->printTotalsConfig($dayConfig);
            $user = User::find(2);
            $user2 = User::find(77);
            Mail::to($user)->cc($user2)->send(new PurchaseOrder($dayConfig));
            return $dayConfig;
        }
    }

    public function sendReminder() {
        $date = date_create();
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        if ($dayofweek < 5 && $dayofweek > 0) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        } else if ($dayofweek == 5) {
            date_add($date, date_interval_create_from_date_string("3 days"));
        } else {
            return null;
        }
        $tomorrow = date_format($date, "Y-m-d");
        $deliveries = Delivery::where("status", "pending")->with(['user'])->where("delivery", "<", $tomorrow . " 23:59:59")->where("delivery", ">", $tomorrow . " 00:00:00")->get();
        if (count($deliveries) > 0) {
            $followers = [];
            $className = "App\\Services\\EditAlerts";
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
                    "type" => "program_reminder",
                    "user_status" => "normal"
                ];
                $date = date_create($deliveryObj->delivery);
                $date = date_format($date, "Y-m-d");
                $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
            }
        }
    }

    private function calculateRoutesShipping($routes) {
        $totalCost = 0;
        $totalIncomeShipping = 0;
        $totalLunches = 0;
        $scenarioHash = "";
        $scenarioHashId = "";
        if (count($routes) > 0) {
            $scenroute = $routes[0];
            $scenarioHash = $this->generateHash($scenroute->id, $scenroute->created_at);
            $scenarioHashId = $scenroute->id;
        }
        $className = "App\\Services\\Rapigo";
        $rapigo = new $className();
        $className2 = "App\\Services\\Basilikum";
        $basilikum = new $className2();
        foreach ($routes as $route) {
            $stops = $route->stops;
            $queryStops = [];
            $routeCost = self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
            foreach ($stops as $stop) {
                $address = $stop->address;
                $querystop = [
                    "address" => $address->address,
                    "description" => $address->name,
                    "type" => "point",
                    "phone" => $address->phone
                ];
                array_push($queryStops, $querystop);
            }
            if ($route->provider == "Rapigo") {
                $rapigoResponse = $rapigo->getEstimate($queryStops);
            } else if ($route->provider == "Basilikum") {
                $rapigoResponse = $basilikum->getEstimate($queryStops);
            }
            if ((self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST) > $rapigoResponse['price']) {
                $routeCost = $rapigoResponse['price'];
            }
            $route->unit_cost = $routeCost;
            $route->save();
            $route->hash = $this->generateHash($route->id, $route->created_at);
            $totalCost += $routeCost;
            $totalIncomeShipping += $route->unit_price;
            $totalLunches += $route->unit;
        }
        $totalRoutes = count($routes);
        $totalIncome = $totalLunches * self::LUNCH_PROFIT;
        $totalProfit = $totalIncomeShipping + $totalIncome;

        $totalGains = $totalProfit - $totalCost;
        $result = [
            "ShippingCostEstimate" => $totalCost,
            "hoov_income" => $totalIncome,
            "shipping_income" => $totalIncomeShipping,
            "total_income" => $totalProfit,
            "routes" => $totalRoutes,
            "lunches" => $totalLunches,
            "lunch_route" => ($totalLunches / $totalRoutes),
            "day_profit" => $totalGains,
            "scenario_hash" => $scenarioHash,
            "scenarioHashId" => $scenarioHashId
        ];
        if ($result['ShippingCostEstimate'] < $result['shipping_income']) {
            $result['status'] = "success";
        } else {
            $result['status'] = "failure";
        }
        return array("routes" => $routes, "result" => $result);
    }

    private function buildRouteQuery(array $input) {
        $query = Route::query();
        foreach ($input as $column => $values) {
            $query->where($column, $values);
        }
        return $query;
    }

    public function getTotalEstimatedShipping(array $input) {
        $query = $this->buildRouteQuery($input);
        $routes = $query->with(['stops.address'])->orderBy('id', 'asc')->get();
        if ($routes) {
            if (count($routes) > 0) {
                return $this->calculateRoutesShipping($routes);
            }
        }
        return array("routes" => [], "result" => []);
    }

    public function getStructureEmails(array $input) {
        $query = $this->buildRouteQuery($input);
        $routes = $query->with(['deliveries.user'])->get();
        if ($routes) {
            return $this->buildScenario($routes);
        }
        return array("routes" => [], "result" => []);
    }

    public function checkScenario($results, $hash) {
        if (count($results) > 0) {
            $scenroute = $results[0];
            return $this->checkHash($scenroute->id, $scenroute->created_at, $hash);
        }
        return false;
    }

    public function regenerateScenarios() {
        $this->deleteOldData();
        $user = User::find(2);
        $polygons = CoveragePolygon::where('merchant_id', 1299)->where("provider", "Rapigo")->get();
        $this->prepareRoutingSimulation($polygons);
        $polygons = CoveragePolygon::where('merchant_id', 1299)->where("provider", "Basilikum")->get();
        $this->prepareRoutingSimulation($polygons, "Basilikum");
        $this->getShippingCosts($user, "pending");
    }

    public function checkUser(User $user) {
        if ($user->id == 1 || $user->id == 2 || $user->id == 3) {
            return true;
        }
        return false;
    }

    public function buildScenarioRouteId($id, $hash) {
        $routes = Route::where("id", $id)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();

        $checkResult = $this->checkScenario($routes, $hash);
        if ($checkResult) {
            $this->buildScenarioTransit($routes);
        }
    }

    public function buildScenarioPositive($scenario, $provider, $hash) {
        $routes = Route::where("type", $scenario)->where("status", "pending")->where("provider", $provider)->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $this->checkScenario($routes, $hash);
        if ($checkResult) {
            $routes = Route::whereColumn('unit_price', '>', 'unit_cost')->where("status", "pending")->where("type", $scenario)->with(['deliveries.user'])->orderBy('id')->get();
            $this->buildScenarioTransit($routes);
        }
    }

    public function buildCompleteScenario($scenario, $provider, $hash) {
        $routes = Route::where("type", $scenario)->where("status", "pending")->where("provider", $provider)->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $this->checkScenario($routes, $hash);
        if ($checkResult) {
            $this->buildScenarioTransit($routes);
        }
    }

    public function buildScenarioTransit($routes) {
//        dd($results);
        foreach ($routes as $route) {
            $route->status = "enqueue";
            foreach ($route->deliveries as $delivery) {
                $delivery->status = "scheduled";
                $delivery->save();
            }
            $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
            foreach ($stops as $stop) {
                $stop->code = "stop-" . $stop->id;
                $stop->status = "pending";
                $stop->save();
            }
            $route->save();
        }
        return true;
    }

    public function buildScenarioLogistics(User $user, array $input) {
        $query = $this->buildRouteQuery($input);
        $routes = $query->with(['deliveries.user'])->orderBy('id')->get();
        if ($routes) {
            $totalCost = 0;
            $deliveries = $routes[0]->deliveries;
            $config = $this->loadDayConfig($deliveries[0]->delivery);
            $totalIncomeShipping = 0;
            $className = "App\\Services\\Rapigo";
            $rapigo = new $className();
            $className2 = "App\\Services\\Basilikum";
            $basilikum = new $className2();
            $totalLunches = 0;
            foreach ($routes as $route) {
                $routeConfig = $config;
                $deliveries = $route->deliveries;
                $routeConfig['father'] = $this->countConfigElements($deliveries, $routeConfig);
                $routeTotals = $this->printTotalsConfig($routeConfig);
                $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
                $queryStops = [];
                foreach ($stops as $stop) {
                    $stopConfig = $config;
                    $stopDescription = "";
                    $stopDeliveries = $stop->deliveries;
                    $stopConfig['father'] = $this->countConfigElements($stopDeliveries, $stopConfig);
                    $stopTotals = $this->printTotalsConfig($stopConfig);
                    $stop->totals = $stopTotals;
                    foreach ($stop->deliveries as $stopDel) {
                        $delUser = $stopDel->user;
                        $descr = $delUser->firstName . " " . $delUser->lastName . " ";
                        $details = json_decode($stopDel->details, true);
                        if (array_key_exists("pickup", $details)) {
                            if ($details['pickup'] == "envase") {
                                $descr = $descr . "Recoger envase,  ";
                            }
                        }
                        if (array_key_exists("deliver", $details)) {
                            if ($details['deliver'] == "envase") {
                                $descr = $descr . "Envase Retornable,  ";
                            }
                        }
                        if (array_key_exists("factura", $details)) {
                            $descr = $descr . "Enviar factura,  ";
                        }
                        $descr = $descr . "Entregar id: " . $stopDel->id . ".  ";
                        $stopDel->region_name = $descr;
                        $delConfig = $config;
                        $dels = [$stopDel];
                        $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
                        $delTotals = $this->printTotalsConfig($delConfig);
                        $stopDel->totals = $delTotals;

                        $stopDescription = $stopDescription . $descr;
                    }
                    $address = $stop->address;
                    if ($stop->stop_order == 1) {
                        $stopDescription = "Recoger los almuerzos de la ruta: " . $route->id;
                    }
                    if ($stop->stop_order == 3) {
                        $stopDescription = "Entregar los envases de la ruta: " . $route->id;
                    }
                    $querystop = [
                        "address" => $address->address . " " . $address->notes,
                        "description" => $stopDescription,
                        "type" => "point",
                        "phone" => $address->phone
                    ];
                    array_push($queryStops, $querystop);
                }
                if ($route->provider == "Rapigo") {
                    $route = $rapigo->createRoute($queryStops, $route, $stops);
                } else if ($route->provider == "Basilikum") {
                    $route = $basilikum->createRoute($queryStops, $route, $stops);
                }
                $route->totals = $routeTotals;
                $totalCost += $route->unit_cost;
                $totalIncomeShipping += $route->unit_price;
                $totalLunches += $route->unit;
                $totalDeliveries = [];
                foreach ($route->deliveries as $del) {
                    $delConfig = $config;
                    $del->status = "preparing";
                    $del->save();
                    $dels = [$del];
                    $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
                    $delTotals = $this->printTotalsConfig($delConfig);
                    $del->totals = $delTotals;
                }
            }

            Mail::to($user)->send(new RouteOrganize($routes));
            Mail::to($user)->send(new RouteDeliver($routes));
            $totalRoutes = count($routes);
            $totalIncome = $totalLunches * self::LUNCH_PROFIT;
            $totalProfit = $totalIncomeShipping + $totalIncome;

            $totalGains = $totalProfit - $totalCost;
            $result = [
                "ShippingCostEstimate" => $totalCost,
                "hoov_income" => $totalIncome,
                "shipping_income" => $totalIncomeShipping,
                "total_income" => $totalProfit,
                "routes" => $totalRoutes,
                "lunches" => $totalLunches,
                "lunch_route" => ($totalLunches / $totalRoutes),
                "day_profit" => $totalGains,
            ];
            if ($result['ShippingCostEstimate'] < $result['shipping_income']) {
                $result['status'] = "success";
            } else {
                $result['status'] = "failure";
            }
            return $result;
        }
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

    public function buildScenario($routes) {
        if ($routes) {
            $deliveries = $routes[0]->deliveries;
            $config = $this->loadDayConfig($deliveries[0]->delivery);
            foreach ($routes as $route) {
                $routeConfig = $config;
                $deliveries = $route->deliveries;
                $routeConfig['father'] = $this->countConfigElements($deliveries, $routeConfig);
                $routeTotals = $this->printTotalsConfig($routeConfig);
                $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
                $queryStops = [];
                foreach ($stops as $stop) {
                    //$stopDeliveries = $stop->deliveries;
                    $deliveries = "";
                    $stopConfig = $config;
                    $stopConfig['father'] = $this->countConfigElements($stop->deliveries, $stopConfig);
                    $stopTotals = $this->printTotalsConfig($stopConfig);
                    $stop->totals = $stopTotals;
                    foreach ($stop->deliveries as $stopDel) {
                        $details = json_decode($stopDel->details, true);
                        $delConfig = $config;
                        $dels = [$stopDel];
                        $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
                        $delTotals = $this->printTotalsConfig($delConfig);
                        $stopDel->totals = $delTotals;
                        $delUser = $stopDel->user;
                        $descr = "Usuario: " . $delUser->firstName . " " . $delUser->lastName . " ";
                        if (array_key_exists("pickup", $details)) {
                            if ($details['pickup'] == "envase") {
                                $descr = $descr . "Recoger envase, ";
                            }
                        }
                        $descr = $descr . "Entregar id: " . $stopDel->id . ".<br/> ";
                        $stopDel->region_name = $descr;
                        $deliveries = $deliveries . $descr;
                    }

                    if ($stop->stop_order == 2) {
                        $stop->region_name = $deliveries;
                    }
                }
                $totalDeliveries = [];
                foreach ($route->deliveries as $del) {
                    $delConfig = $config;
                    $dels = [$del];
                    $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
                    $delTotals = $this->printTotalsConfig($delConfig);
                    $del->totals = $delTotals;
                }

                $route->hash = $this->generateHash($route->id, $route->created_at);
                $route->totals = $routeTotals;
                $route->stops = $stops;
            }
            return $routes;
        }

        //dd($results);
    }

    public function buildScenarioCredits(Route $route) {
        $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
        foreach ($stops as $stop) {
            //$stopDeliveries = $stop->deliveries;
            $deliveries = "";
            foreach ($stop->deliveries as $stopDel) {
                $details = json_decode($stopDel->details, true);
                $delUser = $stopDel->user;
                $descr = "Usuario: " . $delUser->firstName . " " . $delUser->lastName . " ";
                if (array_key_exists("pickup", $details)) {
                    if ($details['pickup'] == "envase") {
                        $descr = $descr . "Recoger envase, ";
                    }
                }
                $descr = $descr . "Entregar id: " . $stopDel->id . ".<br/> ";
                $stopDel->region_name = $descr;
                $stopDel->hash = $this->generateHash($stopDel->id, $stopDel->created_at);
                $stopDel->detauls = $details;
                $deliveries = $deliveries . $descr;
            }
            if ($stop->stop_order == 2) {
                $stop->region_name = $deliveries;
            }
        }
        $route->stops = $stops;
        return [$route];
    }

    public function prepareRouteModel($results, $preOrganize, CoveragePolygon $polygon) {
//        dd($thedata);
        $date = date_create();
        $datelimit = date_format($date, "Y-m-d");
        $now = date_format($date, "Y-m-d H:i:s");
        $dateTimestampLimit = strtotime($datelimit . " 12:00:00");
        $dateTimestampNow = strtotime($now);
        if ($dateTimestampNow > $dateTimestampLimit) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        }
        $la = date_format($date, "Y-m-d");
        $thedata = [
            'lat' => $polygon->lat,
            'lat2' => $polygon->lat,
            'long' => $polygon->long,
            'polygon' => $polygon->id,
            'provider' => $polygon->provider,
            'date1' => $la . " 00:00:00",
            'date2' => $la . " 23:59:59",
        ];

        $deliveries = DB::select(""
                        . "SELECT DISTINCT(d.id), d.delivery,d.provider,d.details,d.user_id,a.address,d.address_id,status,shipping, lat,`long`, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  lat  ) ) ) ) AS Distance 
                   FROM deliveries d join order_addresses a on d.address_id = a.id
                    WHERE
                        status = 'enqueue' AND provider = :provider
                        AND d.delivery >= :date1 AND d.delivery <= :date2 
                            AND a.polygon_id = :polygon order by Distance asc"
                        . "", $thedata);
        if ($polygon->provider == "Basilikum") {
            //dd($deliveries);
        }
        //echo "Query params: ". json_encode($thedata). PHP_EOL;
        //echo "Query results: " . count($deliveries) . PHP_EOL;
        $stops = $this->turnDeliveriesIntoStops($deliveries, $preOrganize);
        if ($polygon->provider == "Basilikum") {
            //dd($stops);
        }
        if ($preOrganize) {
            $results = $this->createRoutes($stops, $results, 'preorganize', true, $polygon->provider);
        } else {
            $results = $this->createRoutes($stops, $results, 'simple', true, $polygon->provider);
        }
        //dd($results);
        return $results;
    }

    public function getLargestAddresses() {
        $results = DB::select(""
                        . "SELECT count(d.id) as total,oa.address, d.provider,d.delivery,d.merchant_id FROM deliveries d "
                        . "join order_addresses oa on oa.id = d.address_id "
                        . "where d.status='enqueue' group by oa.address,d.provider limit 50;");
        return $results;
    }

    public function delegateDeliveries($data) {
        $className = "App\\Services\\Geolocation";
        $address = OrderAddress::where("address", $data['address'])->first();
        $geo = new $className();

        $resultsGeo = $geo->checkMerchantPolygons($address->lat, $address->long, $data['merchant_id'], $data['provider']);
        if (array_key_exists('polygon', $resultsGeo)) {
            $polygon = $resultsGeo['polygon'];
            $thedata = ["address" => $data['address'], "provider" => $data['provider']];
            $thedata2 = ["address" => $data['address']];
            $results = DB::statement(""
                            . " UPDATE deliveries set provider=:provider where address_id in (Select id from order_addresses where address = :address );", $thedata);
            DB::statement(""
                    . " UPDATE order_addresses set polygon_id=$polygon->id where address = :address ;", $thedata2);

            dispatch(new RegenerateScenarios());
            return $results;
        }
        return $resultsGeo;
    }

    public function prepareQuadrantLimits($lat, $long) {
//        $R = 6371;
//        if ($x < 4) {
//            $radiusInf = 0;
//            $radius = 7;
//        } else {
//            $radiusInf = 3;
//            $radius = 7;
//        }
        $thedata = [
            'lat' => $lat,
            'lat2' => $lat,
            'long' => $long
        ];
        return $thedata;
    }

    private function addToExistingStop(Stop $itemSavedStop, Route $route, $stopContainer) {
        $stopDetails = json_decode($itemSavedStop->details, true);
        if (array_key_exists("pickups", $stopDetails)) {
            $stopDetails['pickups'] = array_merge($stopDetails['pickups'], $stopContainer["pickups"]);
            $itemSavedStop->details = json_encode($stopDetails);
        }
        $itemSavedStop->amount += $stopContainer['amount'];
        $itemSavedStop->shipping += $stopContainer['shipping'];
        foreach ($stopContainer["deliveries"] as $del) {
            $realDel2 = Delivery::find($del->id);
            $itemSavedStop->deliveries()->save($realDel2);
            $route->deliveries()->save($realDel2);
        }
        $itemSavedStop->save();
    }

    private function addToNewStop(Route $route, $stopContainer) {
        $details = ["pickups" => []];
        $details['pickups'] = $stopContainer["pickups"];
        $stop = Stop::create([
                    "address_id" => $stopContainer["address_id"],
                    "amount" => $stopContainer["amount"],
                    "shipping" => $stopContainer["shipping"],
                    "route_id" => $route->id,
                    "stop_order" => 2,
                    "details" => json_encode($details)
        ]);
        foreach ($stopContainer["deliveries"] as $del) {
            $realDel = Delivery::find($del->id);
            $stop->deliveries()->save($realDel);
            $route->deliveries()->save($realDel);
        }
    }

    private function addToNewRouteAndStop($save, $scenario, $stopContainer, $shipping) {
        $route = new Route();
        $routestops = [$stopContainer];
        $route->status = 'pending';
        $route->description = $scenario;
        $route->type = $scenario;
        $route->provider = $shipping;
        $serviceBookResponse = [];
        $location = [
            "runner" => "",
            "runner_phone" => "",
            "lat" => 0,
            "long" => 0
        ];
        $serviceBookResponse["location"] = $location;
        $route->coverage = json_encode($serviceBookResponse);
        $route->unit = $stopContainer['amount'];
        $route->height = 1;
        $route->unit_price = $stopContainer['shipping'];
        if ($save) {
            $route->save();
            $address = OrderAddress::create([
                        "user_id" => 1,
                        "name" => "test",
                        "city_id" => 524,
                        "region_id" => 11,
                        "country_id" => 1,
                        "address" => "Carrera 58 D # 130 A - 43",
                        "lat" => 4.721717,
                        "long" => -74.069855,
                        "phone" => "3202261525"
            ]);
            $details = ["pickups" => []];

            $firstStop = Stop::create([
                        "address_id" => $address->id,
                        "amount" => 0,
                        "shipping" => 0,
                        "route_id" => $route->id,
                        "stop_order" => 1,
                        "details" => json_encode($details),
                        "region_name" => "Recoger los almuerzos de la ruta " . $route->id,
            ]);

            $details['pickups'] = $stopContainer["pickups"];
            $stopCreated = Stop::create([
                        "address_id" => $stopContainer["address_id"],
                        "amount" => $stopContainer["amount"],
                        "shipping" => $stopContainer['shipping'],
                        "route_id" => $route->id,
                        "stop_order" => 2,
                        "details" => json_encode($details)
            ]);
            foreach ($stopContainer["deliveries"] as $del) {
                $realDel = Delivery::find($del->id);
                $stopCreated->deliveries()->save($realDel);
                $route->deliveries()->save($realDel);
            }
        } else {
            $route->stops2 = $routestops;
            $route->deliveries2 = $stopContainer["deliveries"];
        }
        return $route;
    }

    private function addStopToRoute(Route $route, $save, $stopContainer) {
        $route->unit += $stopContainer['amount'];
        $route->unit_price += $stopContainer['shipping'];
        if ($save) {
            $itemSavedStop = $route->stops()->where("address_id", $stopContainer["address_id"])->where("route_id", $route->id)->where("stop_order", 2)->first();
            if ($itemSavedStop) {
                $this->addToExistingStop($itemSavedStop, $route, $stopContainer);
            } else {
                $this->addToNewStop($route, $stopContainer);
            }
            $route->height++;
            $route->save();
        } else {
            $routeStops = $route->stops2;
            $foundInRoutes = false;
            foreach ($routeStops as $rts) {
                if ($rts['address_id'] == $stopContainer["address_id"]) {
                    $foundInRoutes = true;
                    $key = array_search($rts, $routeStops);
                    $routeStops[$key]['amount'] += $stopContainer['amount'];
                }
            }
            if (!$foundInRoutes) {
                array_push($routeStops, $stopContainer);
                $route->height++;
            }
            $route->stops2 = $routeStops;
            $route->deliveries2 = array_merge($route->deliveries2, $stopContainer["deliveries"]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function createRoutes($stops, $routes, $scenario, $save, $shipping) {
        foreach ($stops as $stopContainer) {
            $found = false;
            foreach ($routes as $route) {
                $available = self::LUNCH_ROUTE - $route->unit;
                if ($shipping == "Basilikum") {
                    $available = 100 - $route->unit;
                }

                if (($available > 0 && $available >= $stopContainer['amount']) && !$found) {
                    $found = true;
                    $this->addStopToRoute($route, $save, $stopContainer);
                    break;
                }
            }
            if ($found == false) {
                $route = $this->addToNewRouteAndStop($save, $scenario, $stopContainer, $shipping);
                array_unshift($routes, $route);
            }
        }
        return $routes;
    }

    private static function cmp($a, $b) {
        if ($a['amount'] == $b['amount']) {
            return 0;
        }
        return ($a['amount'] < $b['amount']) ? -1 : 1;
    }

    private function addpickupStop(Route $route) {
        $address = OrderAddress::create([
                    "user_id" => 1,
                    "name" => "test",
                    "city_id" => 524,
                    "region_id" => 11,
                    "country_id" => 1,
                    "address" => "Carrera 58 D # 130 A - 43",
                    "lat" => 4.721717,
                    "long" => -74.069855,
                    "phone" => "3202261525"
        ]);
        $details = ["pickups" => []];
        $firstStop = Stop::create([
                    "address_id" => $address->id,
                    "amount" => 0,
                    "route_id" => $route->id,
                    "region_name" => "Entregar los envases recogidos",
                    "stop_order" => 3,
                    "details" => json_encode($details)
        ]);
    }

    public function completeRoutes($routes) {
        foreach ($routes as $route) {
            $pickup = "";
            $addReturn = false;
            $stops = $route->stops;
            foreach ($stops as $stop) {
                if ($stop->stop_order != 1) {
                    $attributes = json_decode($stop->details, true);
                    if (array_key_exists("pickups", $attributes)) {
                        if (count($attributes['pickups']) > 0) {
                            $addReturn = true;
                        }
                    }
                }
            }
            if ($addReturn) {
                $this->addpickupStop($route);
            }
        }
    }

    public function generateScenario(User $user, array $data) {
        $totalCost = 0;
        $totalCost2 = 0;
        $totalIncome = 0;
        $totalIncomeShipping = 0;
        $totalProfit = 0;
        $totalRoutes = 0;
        $totalLunches = 0;
        $totalGains = 0;
        $routes = $data['routes'];

        foreach ($routes as $rt) {
            $route = Route::create([
                        "status" => "pending",
                        "description" => "user",
                        "user_id" => $user->id,
                        "availability" => $data['date']
            ]);
            $totalRoutes++;
            $stops = $rt['stops'];
            $stopsnum = count($stops);
            foreach ($stops as $st) {
                $stop = Stop::create([
                            "route_id" => $route->id,
                            "address_id" => $st['address_id'],
                ]);
                $deliveries = $st['deliveries'];
                foreach ($deliveries as $del) {
                    $totalLunches++;
                    $delivery = Delivery::find($del['id']);
                    $route->deliveries()->save($delivery);
                    $stop->deliveries()->save($delivery);
                }
            }
            if ($stopsnum > 4) {
                $totalCost += self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
            } else {
                $totalCost += self::ROUTE_HOUR_COST * 2;
            }
            $totalIncomeShipping += $rt->unit_price;
            if (($stopsnum + 1) > 7) {
                $totalCost2 += ($stopsnum + 1) * 5400;
            } else {
                $totalCost2 += ($stopsnum + 1) * 6400;
            }
        }
        $result = [
            "hourly_cost" => $totalCost,
            "stops_cost" => $totalCost2,
            "hoov_income" => $totalIncome,
            "shipping_income" => $totalIncomeShipping,
            "total_income" => $totalProfit,
            "routes" => $totalRoutes,
            "lunches" => $totalLunches,
            "lunch_route" => ($totalLunches / $totalRoutes),
            "profit" => $totalGains,
        ];
    }

    public function showScenario() {
        
    }

    public function deleteUserScenario(User $user, $type, $date) {
        $routes = Route::where("user_id", $user->id)->where("availability", $date)->get();
        foreach ($routes as $rt) {

            DB::table('delivery_route')
                    ->where('route_id', $rt->id)
                    ->delete();
            $stops = $rt->stops;
            $stopsnum = 0;
            foreach ($stops as $st) {
                DB::table('delivery_stop')
                        ->where('stop_id', $st->id)
                        ->delete();
            }
            $rt->stops()->delete();
            $rt->delete();
        }
    }

    public function getShippingCostArray() {
        return [
            "1" => 3050.00,
            "2" => 2800.00,
            "3" => 2800.00,
            "4" => 2700.00,
            "5" => 2700.00,
            "6" => 2600.00,
            "7" => 2500.00,
            "8" => 2500.00,
            "9" => 2400.00,
            "10" => 2200.00,
            "11" => 2000.00,
        ];
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

    public function deleteRandomDeliveriesData() {
        $deliveries = Delivery::where("user_id", 5)->get();
        foreach ($deliveries as $item) {
            DB::table('delivery_stop')
                    ->where('delivery_id', $item->id)
                    ->delete();
            DB::table('delivery_route')
                    ->where('delivery_id', $item->id)
                    ->delete();
            $item->delete();
        }
        $routes = Route::whereIn("status", ["pending", "enqueue"])->get();
        foreach ($routes as $value) {
            $value->stops()->delete();
        }
        Route::where("status", "pending")->delete();
        Route::where("status", "enqueue")->delete();

        OrderAddress::where("name", "test")->delete();
    }

    public function generateRandomDeliveries(CoveragePolygon $polygon) {
        $lat = $polygon->lat;
        $long = $polygon->long;
        $radius = 1; // in miles
        $addresses = ["Carrera 7a # 64-44", "Calle 73 # 0 - 24", "Calle 53 # 5 - 74", "Calle 23 # 7 - 74", "Cra 9 # 77-67", "Cra 9 # 71-32", "Cra 9 # 71-32"];
        $R = 6371;
        $lat_max = ($lat + rad2deg($radius / $R)) * 1000000000;
        $lat_min = ($lat - rad2deg($radius / $R)) * 1000000000;
        $lng_max = ($long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)))) * 1000000000;
        $lng_min = ($long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)))) * 1000000000;
        $date = date_create();
        date_add($date, date_interval_create_from_date_string("1 days"));
        $shipping = $this->getShippingCostArray();

        $articles = Article::where('start_date', date_format($date, "Y-m-d"))->get();
        //$articles = Article::where('start_date', "2019-10-02")->get();
        //$date = date_create();
        $total = rand(1, 3);
        for ($x = 0; $x <= $total; $x++) {
            $latit = rand($lat_min, $lat_max) / 1000000000;
            $longit = rand($lng_min, $lng_max) / 1000000000;
            $addressIndex = rand(0, 6);

            $address = OrderAddress::create([
                        "user_id" => 5,
                        "name" => "test",
                        "city_id" => 524,
                        "region_id" => 11,
                        "country_id" => 1,
                        "polygon_id" => $polygon->id,
                        "address" => $addresses[$addressIndex],
                        "lat" => $latit,
                        "long" => $longit,
                        "phone" => "3105507245"
            ]);
            $amountDeliveries = rand(1, 3);
            for ($j = 0; $j <= $amountDeliveries; $j++) {
                $type_num = rand(0, count($articles) - 1);
                $art = $articles[$type_num];
                $dish = [];
                $attrs = json_decode($art->attributes, true);
                if (count($attrs['entradas']) > 0) {
                    $starter = rand(0, count($attrs['entradas']) - 1);
                    $starterPlate = $attrs['entradas'][$starter];
                    $dish['starter_id'] = $starterPlate['codigo'];
                }
                if (count($attrs['plato']) > 0) {
                    $main = rand(0, count($attrs['plato']) - 1);
                    $mainPlate = $attrs['plato'][$main];
                    $dish['main_id'] = $mainPlate['codigo'];
                }
                $pickingUp = rand(0, 1);
                $pickingUp = 0;
                $details = [];
                if ($pickingUp == 1) {
                    $details['pickup'] = "envase";
                }
                $dish['type_id'] = $art->id;
                $dish['dessert_id'] = null;
                $details['dish'] = $dish;
                $delivery = Delivery::create([
                            "user_id" => 5,
                            "delivery" => date_format($date, "Y-m-d") . " 12:00:00",
                            "provider" => "Rapigo",
                            "shipping" => $shipping[$amountDeliveries],
                            "status" => "enqueue",
                            "merchant_id" => 1299,
                            "address_id" => $address->id,
                            "details" => json_encode($details)
                ]);
            }
        }
        echo 'lng (min/max): ' . $lng_min . '/' . $lng_max . PHP_EOL;
        echo 'lat (min/max): ' . $lat_min . '/' . $lat_max . PHP_EOL;
    }

    public function deleteOldData() {
        $routes = Route::whereIn("status", ["pending", "enqueue"])->get();
        foreach ($routes as $value) {
            DB::table('delivery_route')
                    ->where('route_id', $value->id)
                    ->delete();
            $stops = $value->stops;
            foreach ($stops as $item) {
                DB::table('delivery_stop')
                        ->where('stop_id', $item->id)
                        ->delete();
                $item->delete();
            }
        }
        $routes = Route::whereIn("status", ["pending", "enqueue"])->delete();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function prepareRoutingSimulation($polygons, $shipping = "Rapigo") {
        $results = [];
        $results2 = [];
        foreach ($polygons as $polygon) {
            if ($shipping == "Rapigo") {
                $results2 = $this->prepareRouteModel($results2, false, $polygon, $shipping);
            }
            $results = $this->prepareRouteModel($results, true, $polygon, $shipping);
        }
        $this->completeRoutes($results);
        if ($shipping == "Rapigo") {
            $this->completeRoutes($results2);
        }
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

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getShippingCosts($user, $status) {
        $providers = ["Rapigo", "Basilikum"];
        foreach ($providers as $provider) {
            if ($provider == "Rapigo") {
                $data = [
                    "type" => "preorganize",
                    "status" => $status,
                    "provider" => $provider
                ];

                $resultsPre = $this->getTotalEstimatedShipping($data);
                $resultsPre = $resultsPre['result'];
                $resultsPre['route_provider'] = $provider;
                $resultsPre['route_status'] = "pending";
                $data = [
                    "type" => "simple",
                    "status" => $status,
                    "provider" => $provider
                ];
                $resultsSimple = $this->getTotalEstimatedShipping($data);
                $resultsSimple = $resultsSimple['result'];
                $resultsSimple['route_provider'] = $provider;
                $resultsSimple['route_status'] = "pending";
            } else if ($provider == "Basilikum") {
                $data = [
                    "status" => $status,
                    "provider" => $provider
                ];
                $resultsPre = $this->getTotalEstimatedShipping($data);
                $resultsPre = $resultsPre['result'];
                $resultsPre['route_provider'] = $provider;
                $resultsPre['route_status'] = "pending";
                $resultsSimple = $resultsPre;
            }
            $winningScenario = "";
            if (!array_key_exists("day_profit", $resultsPre)) {
                continue;
            }
            if ($resultsPre["day_profit"] > $resultsSimple["day_profit"]) {
                $winningScenario = "Preorganize";
            } else {
                $winningScenario = "Simple";
            }

            Mail::to($user)->send(new ScenarioSelect($resultsPre, $resultsSimple, $winningScenario));
        }
        return array("status" => "success", "message" => "costs sent");
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
            $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
            if ($dayofweek > 0 && $dayofweek < 5) {
                date_add($date, date_interval_create_from_date_string("1 days"));
            } else if ($dayofweek == 5) {
                date_add($date, date_interval_create_from_date_string("3 days"));
            } else if ($dayofweek == 6) {
                date_add($date, date_interval_create_from_date_string("2 days"));
            } else if ($dayofweek == 7) {
                date_add($date, date_interval_create_from_date_string("1 days"));
            }
            if ($delivery) {
                if ($delivery->status == "deposit") {
                    $item->delivery = $delivery->delivery;
                    $delivery->delivery = $date;
                    $delivery->save();
                } else {
                    $item->delivery = $date;
                }
                if ($delivery2) {
                    $tempAttrs = $delivery2->details;
                    $delivery2->details = $item->details;
                    $delivery2->save();
                    $item->details = $tempAttrs;
                }
            } else {
                $item->delivery = $date;
            }
            $item->save();
        }
    }

}
